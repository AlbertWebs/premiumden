<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MembershipPackage;

class StoreMembershipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $premiumPackage = MembershipPackage::query()->whereKey($this->input('membership_package_id'))->whereIn('slug', ['gold', 'platinum'])->exists();
        $requiredForSubmission = $this->boolean('save_draft') ? 'nullable' : ($premiumPackage ? 'required' : 'nullable');

        return [
            'membership_package_id' => ['required', 'integer', Rule::exists('membership_packages', 'id')->where('is_active', true)],
            'save_draft' => ['nullable', 'boolean'],
            'draft_reference' => ['nullable', 'string', 'max:24'],
            'draft_token' => ['nullable', 'string', 'size:64'],
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'company' => ['required', 'string', 'max:180'],
            'job_title' => ['required', 'string', 'max:140'],
            'industry' => ['required', 'string', 'max:140'],
            'location' => ['required', 'string', 'max:140'],
            'biography' => ['nullable', 'string', 'max:2000'],
            'company_description' => [$requiredForSubmission, 'string', 'max:2500'],
            'employee_count' => [$requiredForSubmission, Rule::in(['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'])],
            'company_profile_file' => [$requiredForSubmission, 'file', 'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,webp', 'max:20480'],
            'references' => ['required', 'array', 'size:2'],
            'references.*.full_name' => ['required', 'string', 'max:160'],
            'references.*.membership_number' => ['required', 'string', 'max:40', 'distinct:ignore_case'],
            'consent' => $this->boolean('save_draft') ? ['nullable'] : ['accepted'],
        ];
    }

    public function messages(): array
    {
            return ['references.size' => 'Please provide two member references.', 'references.*.membership_number.required' => 'Enter the member’s membership number.', 'consent.accepted' => 'Please confirm that the information is accurate and consent to the application review.'];
    }
}
