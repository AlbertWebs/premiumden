<?php

namespace App\Http\Controllers\Admin;

use App\Models\MemberDeal;
use App\Models\MemberDealApplication;
use App\Models\MemberDealDocument;
use App\Models\MembershipPackage;
use App\Services\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberDealController
{
    public function index(): View
    {
        return view('admin.deals.index', ['deals' => MemberDeal::with(['targetPackage'])->withCount(['applications', 'documents'])->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return $this->form(new MemberDeal);
    }

    public function store(Request $request, RecordAuditEvent $audit): RedirectResponse
    {
        return $this->save($request, new MemberDeal, $audit);
    }

    public function edit(MemberDeal $deal): View
    {
        return $this->form($deal);
    }

    public function update(Request $request, MemberDeal $deal, RecordAuditEvent $audit): RedirectResponse
    {
        return $this->save($request, $deal, $audit);
    }

    public function applications(MemberDeal $deal): View
    {
        return view('admin.deals.applications', ['deal' => $deal, 'applications' => $deal->applications()->with('member.membershipPackage')->latest('applied_at')->paginate(20)]);
    }

    public function document(MemberDeal $deal, MemberDealDocument $document)
    {
        abort_unless($document->member_deal_id === $deal->id, 404);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    public function destroyDocument(Request $request, MemberDeal $deal, MemberDealDocument $document, RecordAuditEvent $audit): RedirectResponse
    {
        abort_unless($document->member_deal_id === $deal->id, 404);
        Storage::disk('local')->delete($document->path);
        $audit->handle('member_deal.document_deleted', $document, ['deal_id' => $deal->id], $request->user()->id);
        $document->delete();

        return back()->with('status', 'Deal document removed.');
    }

    public function updateApplication(Request $request, MemberDeal $deal, MemberDealApplication $application, RecordAuditEvent $audit): RedirectResponse
    {
        abort_unless($application->member_deal_id === $deal->id, 404);
        $data = $request->validate(['status' => ['required', 'in:pending,in_review,referred,accepted,declined']]);
        $application->update($data);
        $audit->handle('member_deal.application_status_changed', $application, ['status' => $data['status'], 'deal_id' => $deal->id], $request->user()->id);

        return back()->with('status', 'Deal application status updated.');
    }

    private function save(Request $request, MemberDeal $deal, RecordAuditEvent $audit): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'partner' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:80'],
            'summary' => ['required', 'string', 'max:500'],
            'details' => ['required', 'string', 'max:12000'],
            'member_value' => ['required', 'string', 'max:5000'],
            'application_instructions' => ['nullable', 'string', 'max:5000'],
            'minimum_package_id' => ['nullable', 'integer', Rule::exists('membership_packages', 'id')->where('is_active', true)],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,csv,jpg,jpeg,png', 'max:10240'],
            'starts_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
        $uploads = $data['documents'] ?? [];
        unset($data['documents']);
        $slug = Str::slug($data['title']);
        if (MemberDeal::where('slug', $slug)->where('id', '!=', $deal->id ?? 0)->exists()) {
            $slug .= '-'.Str::lower(Str::random(5));
        }
        $data['slug'] = $slug;
        $data['created_by'] = $deal->exists ? $deal->created_by : $request->user()->id;
        $storedPaths = [];
        try {
            DB::transaction(function () use ($deal, $data, $uploads, &$storedPaths): void {
                $deal->fill($data)->save();
                foreach ($uploads as $upload) {
                    $path = $upload->store('member-deals', 'local');
                    if (! $path) {
                        throw new \RuntimeException('Unable to store a deal document.');
                    }
                    $storedPaths[] = $path;
                    $deal->documents()->create([
                        'original_name' => mb_substr(basename($upload->getClientOriginalName()), 0, 255),
                        'path' => $path,
                        'mime_type' => $upload->getMimeType(),
                        'size_bytes' => $upload->getSize(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }
        $audit->handle('member_deal.saved', $deal, ['active' => $deal->is_active, 'minimum_package_id' => $deal->minimum_package_id, 'documents_added' => count($uploads)], $request->user()->id);

        return redirect()->route('admin.deals.index')->with('status', 'Member deal saved.');
    }

    private function form(MemberDeal $deal): View
    {
        $deal->loadMissing(['targetPackage', 'documents']);

        return view('admin.deals.edit', [
            'deal' => $deal,
            'packages' => MembershipPackage::where('is_active', true)->orderBy('networking_level')->get(),
        ]);
    }
}
