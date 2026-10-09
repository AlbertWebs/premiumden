<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach(['home', 'about', 'team', 'membership', 'how-to-join', 'articles.index', 'authors.index', 'events.index', 'activities.index', 'contact', 'privacy', 'terms'] as $routeName)
        <url><loc>{{ route($routeName) }}</loc><changefreq>monthly</changefreq><priority>{{ $routeName === 'home' ? '1.0' : '0.6' }}</priority></url>
    @endforeach
    @foreach($articles as $article)
        <url><loc>{{ route('articles.show', $article->slug) }}</loc><lastmod>{{ $article->updated_at->toAtomString() }}</lastmod><changefreq>yearly</changefreq><priority>0.7</priority></url>
    @endforeach
    @foreach($events as $event)
        <url><loc>{{ route('events.show', $event->slug) }}</loc><lastmod>{{ $event->updated_at->toAtomString() }}</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>
    @endforeach
    @foreach($activities as $activity)
        <url><loc>{{ route('activities.show', $activity->slug) }}</loc><lastmod>{{ $activity->updated_at->toAtomString() }}</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>
    @endforeach
</urlset>
