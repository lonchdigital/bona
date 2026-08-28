{{--
    The package's own url template, with two tags taken out.

    changefreq and priority carry a default of "daily" and 0.8 on every Url,
    and the properties are typed so they cannot be unset from the calling
    code. Google has said for years that it reads neither — so on 2312
    addresses they were a third of the file saying nothing at all.

    lastmod stays: that one Google does read, to decide what is worth
    fetching again.
--}}
<url>
    @if (! empty($tag->url))
    <loc>{{ url($tag->url) }}</loc>
    @endif
@if (count($tag->alternates))
@foreach ($tag->alternates as $alternate)
    <xhtml:link rel="alternate" hreflang="{{ $alternate->locale }}" href="{{ url($alternate->url) }}" />
    @endforeach
@endif
@if (! empty($tag->lastModificationDate))
    <lastmod>{{ $tag->lastModificationDate->format(DateTime::ATOM) }}</lastmod>
@endif
    @each('sitemap::image', $tag->images, 'image')
    @each('sitemap::video', $tag->videos, 'video')
    @each('sitemap::news', $tag->news, 'news')
</url>
