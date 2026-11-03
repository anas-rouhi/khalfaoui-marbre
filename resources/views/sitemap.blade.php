{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach ($pages as $page)
    <url>
        <loc>{{ $page }}</loc>
        <lastmod>{{ $lastModified }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
@foreach ($alternates as $lang => $href)
        <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}"/>
@endforeach
@if ($loop->first)
@foreach ($images as $image)
        <image:image>
            <image:loc>{{ $image['loc'] }}</image:loc>
            <image:title>{{ $image['title'] }}</image:title>
        </image:image>
@endforeach
@endif
    </url>
@endforeach
</urlset>
