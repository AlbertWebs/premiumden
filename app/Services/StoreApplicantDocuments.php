<?php

namespace App\Services;

use App\Models\MembershipApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreApplicantDocuments
{
    public function handle(MembershipApplication $application, array $files, ?string $identityType): void
    {
        $stored = [];
        $replaced = [];

        try {
            DB::transaction(function () use ($application, $files, $identityType, &$stored, &$replaced): void {
                foreach (['photo' => 'photo', 'identity' => 'identity'] as $input => $documentType) {
                    $file = $files[$input] ?? null;
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }

                    $extension = $file->guessExtension() ?: 'bin';
                    $path = $file->storeAs('applications/'.$application->id.'/documents', Str::uuid().'.'.$extension, 'local');
                    $stored[] = $path;
                    $previous = $application->documents()->where('document_type', $documentType)->value('storage_path');
                    if ($previous) {
                        $replaced[] = $previous;
                    }
                    $application->documents()->updateOrCreate(['document_type' => $documentType], [
                        'identity_type' => $input === 'identity' ? $identityType : null,
                        'original_name' => mb_substr($file->getClientOriginalName(), 0, 180),
                        'storage_path' => $path,
                        'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                        'size_bytes' => $file->getSize(),
                        'status' => 'pending',
                        'reviewed_by' => null,
                        'review_note' => null,
                        'reviewed_at' => null,
                    ]);
                }
            });
            Storage::disk('local')->delete($replaced);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($stored);
            throw $exception;
        }
    }
}
