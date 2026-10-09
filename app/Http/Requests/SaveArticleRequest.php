<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('author')
            || $this->user()?->hasRole('content_administrator')
            || $this->user()?->hasRole('super_administrator');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash', Rule::unique('articles', 'slug')->ignore($this->route('article'))],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:50000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'category_id' => ['nullable', 'integer', 'exists:article_categories,id'],
            'tags' => ['nullable', 'string', 'max:500'],
            'author_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'author')],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:300'],
        ];
    }
}
