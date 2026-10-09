<?php

namespace App\Http\Controllers\Admin;

use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxonomyController
{
    public function index(): View
    {
        return view('admin.taxonomy.index', [
            'categories' => ArticleCategory::withCount('articles')->orderBy('display_order')->get(),
            'tags' => ArticleTag::withCount('articles')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse { return $this->saveCategory($request, new ArticleCategory); }
    public function updateCategory(Request $request, ArticleCategory $category): RedirectResponse { return $this->saveCategory($request, $category); }

    public function storeTag(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $slug = Str::slug($data['name']);
        if (! $slug) { return back()->withErrors(['name' => 'Enter a tag with letters or numbers.'])->withInput(); }
        ArticleTag::firstOrCreate(['slug' => $slug], ['name' => $data['name']]);
        return back()->with('status', 'Article tag saved.');
    }

    private function saveCategory(Request $request, ArticleCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'], 'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('article_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:1000'], 'display_order' => ['required', 'integer', 'between:0,65535'], 'is_active' => ['required', 'boolean'],
        ]);
        $base = Str::slug(filled($data['slug'] ?? null) ? $data['slug'] : $data['name']);
        $slug = $base ?: 'category';
        for ($suffix = 2; ArticleCategory::where('slug', $slug)->where('id', '<>', $category->id ?? 0)->exists(); $suffix++) { $slug = $base.'-'.$suffix; }
        $data['slug'] = $slug;
        $category->fill($data)->save();

        return back()->with('status', 'Article category saved.');
    }
}
