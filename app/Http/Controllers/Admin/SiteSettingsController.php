<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SiteSettingsController
{
    private const FIELDS = [
        'public_contact_email' => ['nullable', 'email:rfc', 'max:190'],
        'public_contact_phone' => ['nullable', 'string', 'max:40'],
        'office_location' => ['nullable', 'string', 'max:180'],
        'contact_recipient_email' => ['nullable', 'email:rfc', 'max:190'],
        'default_seo_title' => ['nullable', 'string', 'max:180'],
        'default_seo_description' => ['nullable', 'string', 'max:300'],
        'home_hero_title' => ['nullable', 'string', 'max:180'],
        'home_hero_intro' => ['nullable', 'string', 'max:500'],
        'home_intro_heading' => ['nullable', 'string', 'max:180'],
        'home_intro_body' => ['nullable', 'string', 'max:1500'],
        'why_join_heading' => ['nullable', 'string', 'max:180'],
        'why_join_body' => ['nullable', 'string', 'max:1800'],
        'community_heading' => ['nullable', 'string', 'max:180'],
        'community_body' => ['nullable', 'string', 'max:1800'],
        'membership_heading' => ['nullable', 'string', 'max:180'],
        'membership_body' => ['nullable', 'string', 'max:1500'],
        'membership_disclaimer' => ['nullable', 'string', 'max:800'],
        'about_heading' => ['nullable', 'string', 'max:180'],
        'about_body' => ['nullable', 'string', 'max:3000'],
        'how_to_join_heading' => ['nullable', 'string', 'max:180'],
        'how_to_join_body' => ['nullable', 'string', 'max:2000'],
        'faq_items' => ['nullable', 'string', 'max:6000'],
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', ['settings' => SiteSetting::allValues()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), self::FIELDS);
        $data = $validator->validate();
        DB::transaction(function () use ($data, $request): void {
            foreach (array_keys(self::FIELDS) as $key) {
                SiteSetting::updateOrCreate(['key' => $key], ['value' => $data[$key] ?? null, 'updated_by' => $request->user()->id]);
            }
        });
        SiteSetting::forgetCache();
        app(\App\Services\RecordAuditEvent::class)->handle('site_settings.updated', $request->user(), ['keys' => array_keys($data)], $request->user()->id);
        return back()->with('status', 'Website settings updated.');
    }
}
