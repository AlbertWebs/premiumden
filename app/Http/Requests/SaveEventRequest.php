<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEventRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole('content_administrator') || $this->user()?->hasRole('super_administrator'); }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('events', 'slug')->ignore($this->route('event')?->id)],
            'description' => ['required', 'string', 'max:12000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'location' => ['required', 'string', 'max:180'],
            'starts_at' => ['required', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'visibility' => ['required', Rule::in(['public', 'members', 'packages'])],
            'package_ids' => [Rule::requiredIf($this->input('visibility') === 'packages'), 'nullable', 'array', 'min:1'], 'package_ids.*' => ['integer', 'distinct', 'exists:membership_packages,id'],
            'registration_url' => ['nullable', 'url:https', 'max:2048'],
            'registration_information' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::in(['draft', 'published', 'cancelled'])],
        ];
    }
}
