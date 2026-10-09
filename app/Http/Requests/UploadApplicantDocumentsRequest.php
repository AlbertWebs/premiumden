<?php

namespace App\Http\Requests;

use App\Models\MembershipApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UploadApplicantDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'identity' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'identity_type' => ['required_with:identity', 'nullable', 'in:national_id,passport'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $application = MembershipApplication::query()->where('reference', $this->route('reference'))->first();
            foreach (['photo', 'identity'] as $type) {
                if (! $this->hasFile($type) && ! $application?->documents()->where('document_type', $type)->exists()) {
                    $validator->errors()->add($type, 'Please provide this required document.');
                }
            }
        });
    }
}
