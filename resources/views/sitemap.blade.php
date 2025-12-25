<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
        @if(isset($url['images']))
            @foreach($url['images'] as $image)
        <image:image>
            <image:loc>{{ $image['loc'] }}</image:loc>
            <image:caption>{{ $image['caption'] }}</image:caption>
            <image:title>{{ $image['title'] }}</image:title>
        </image:image>
            @endforeach
        @endif
    </url>
@endforeach
</urlset>
