<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateProfile', $this->user()) ?? false;
    }

    public function rules(): array
    {
        return [
            'company' => ['nullable', 'string', 'max:180'],
            'job_title' => ['nullable', 'string', 'max:140'],
            'industry' => ['nullable', 'string', 'max:140'],
            'business_category' => ['nullable', 'string', 'max:140'],
            'location' => ['nullable', 'string', 'max:140'],
            'interests' => ['nullable', 'string', 'max:500'],
            'biography' => ['nullable', 'string', 'max:2000'],
            'is_listed' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
