@props(['article', 'categories', 'authors' => collect()])
<div class="article-editor-fields"><label for="title">Article title <span>*</span></label><input id="title" name="title" value="{{ old('title', $article->title) }}" maxlength="180" required>@error('title')<span class="field-error">{{ $message }}</span>@enderror
    <div class="form-grid"><div><label for="slug">URL slug <span class="optional">Optional</span></label><input id="slug" name="slug" value="{{ old('slug', $article->slug) }}" maxlength="190">@error('slug')<span class="field-error">{{ $message }}</span>@enderror</div><div><label for="category_id">Category</label><select id="category_id" name="category_id"><option value="">Choose category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $article->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
    @if($authors->isNotEmpty())<div><label for="author_id">Author</label><select id="author_id" name="author_id"><option value="">Current account</option>@foreach($authors as $author)<option value="{{ $author->id }}" @selected(old('author_id', $article->author_id) == $author->id)>{{ $author->name }}</option>@endforeach</select></div>@endif
    <div><label for="tags">Tags <span class="optional">Comma separated</span></label><input id="tags" name="tags" value="{{ old('tags', $article->exists ? $article->tags->pluck('name')->implode(', ') : '') }}" maxlength="500"></div></div>
    <label for="excerpt">Summary</label><textarea id="excerpt" name="excerpt" rows="3" maxlength="500">{{ old('excerpt', $article->excerpt) }}</textarea><div class="article-image-fields"><div><label for="featured_image">Featured image <span class="optional">JPG, PNG or WebP · 5 MB max</span></label><input id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp">@if($article->featured_image_path)<img class="article-image-preview" src="{{ Storage::disk('public')->url($article->featured_image_path) }}" alt="Current featured image">@endif</div><div><label for="og_image">Social share image <span class="optional">Optional</span></label><input id="og_image" name="og_image" type="file" accept="image/jpeg,image/png,image/webp">@if($article->og_image_path)<img class="article-image-preview" src="{{ Storage::disk('public')->url($article->og_image_path) }}" alt="Current social share image">@endif</div></div><label for="body">Article body <span>*</span></label>
    <div class="article-rich-editor" data-article-editor>
        <div class="article-rich-toolbar" role="toolbar" aria-label="Article body formatting">
            <button type="button" data-editor-command="bold" aria-label="Bold" title="Bold"><strong>B</strong></button>
            <button type="button" data-editor-command="italic" aria-label="Italic" title="Italic"><em>I</em></button>
            <span class="toolbar-divider" aria-hidden="true"></span>
            <button type="button" data-editor-command="formatBlock" data-editor-value="&lt;h2&gt;" aria-label="Heading" title="Heading">H2</button>
            <button type="button" data-editor-command="formatBlock" data-editor-value="&lt;h3&gt;" aria-label="Subheading" title="Subheading">H3</button>
            <span class="toolbar-divider" aria-hidden="true"></span>
            <button type="button" data-editor-command="insertUnorderedList" aria-label="Bulleted list" title="Bulleted list">☷</button>
            <button type="button" data-editor-command="insertOrderedList" aria-label="Numbered list" title="Numbered list">1.</button>
            <button type="button" data-editor-command="formatBlock" data-editor-value="&lt;blockquote&gt;" aria-label="Quote" title="Quote">❝</button>
            <button type="button" data-editor-command="removeFormat" aria-label="Clear formatting" title="Clear formatting">Tx</button>
        </div>
        <div class="article-rich-surface" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Article body editor" aria-required="true">{!! app(\App\Services\ArticleBodyFormatter::class)->render(old('body', $article->body ?? '')) !!}</div>
        <textarea id="body" class="article-rich-source" name="body" rows="18">{{ old('body', $article->body) }}</textarea>
    </div>
    @error('body')<span class="field-error">{{ $message }}</span>@enderror
    <p class="article-editor-note">Select text to format it. Headings, emphasis, lists and quotes are available in the toolbar.</p>
    <div class="form-grid"><div><label for="seo_title">Search title <span class="optional">Optional</span></label><input id="seo_title" name="seo_title" value="{{ old('seo_title', $article->seo_title) }}" maxlength="180"></div><div><label for="seo_description">Search description <span class="optional">Optional</span></label><textarea id="seo_description" name="seo_description" rows="2" maxlength="300">{{ old('seo_description', $article->seo_description) }}</textarea></div></div>
</div>
