@extends('layouts.store-main')

@section('title')

    @if(isset($blogArticle))
        @if($blogArticle->meta_title)
            <title>{{ $blogArticle->meta_title }}</title>
            <meta name="title" content="{{ $blogArticle->meta_title }}">
        @else
            <title>{{ $blogArticle->name }}</title>
            <meta name="title" content="{{ $blogArticle->name }}">
        @endif

        @if($blogArticle->meta_description)
            <meta name="description" content="{{ $blogArticle->meta_description }}">
        @else
            <meta name="description" content="{{ $blogArticle->preview_text }}">
        @endif

        @if($blogArticle->meta_keywords)
            <meta name="keywords" content="{{ $blogArticle->meta_keywords }}">
        @endif

        @if($blogArticle->meta_tags)
            {!! $blogArticle->meta_tags !!}
        @endif

        <meta property="og:title" content="{{ $blogArticle->name . ' - ' . trans('base.site_title') }}">
        <meta property="og:description" content="{{ $blogArticle->meta_description ?: $blogArticle->preview_text }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $blogArticle->name }}">
        <meta name="twitter:description" content="{{ $blogArticle->meta_description ?: $blogArticle->preview_text }}">
        @if($blogArticle->og_image_url)
            <meta name="twitter:image" content="{{ $blogArticle->og_image_url }}">
        @endif

    @endif

@endsection

{{-- Without these the layout falls back to the 32x32 favicon, which is what
     messengers kept showing for every shared article. --}}
@section('og_type', 'article')

@if($blogArticle->og_image_url)
    @section('og_image', $blogArticle->og_image_url)
@endif

@section('content')

    <!-- ========================  Main header ======================== -->

    @php
        $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
        $blogUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page');
    @endphp

    <section class="main-header main-header-blog" style="background-image:url({{ $blogArticle->hero_image_url }})">
        <header>
            <div class="container">
                <ol class="breadcrumb breadcrumb-inverted font-two art-article-breadcrumb">
                    <li>
                        <a href="{{ $homeUrl }}"><span class="icon icon-home"></span> {{ trans('base.home') }}</a>
                    </li>
                    <li>
                        <a href="{{ $blogUrl }}">{{ trans('base.blog') }}</a>
                    </li>
                    <li>
                        <span class="active">{{ $blogArticle->name }}</span>
                    </li>
                </ol>
            </div>
        </header>
    </section>



    <section class="blog art-single-blog">

        <!-- ========================  Blog post ======================== -->

        @php
            $articleUrl = url()->current();
            $articleDescription = (string) ($blogArticle->meta_description ?: $blogArticle->preview_text);

            $articleAuthor = $articleAuthor ?? null;

            $authorPageUrl = $articleAuthor
                ? App\Helpers\MultiLangRoute::getMultiLangRoute('store.author.page', ['authorSlug' => $articleAuthor->slug])
                : null;

            // The author record is the source of truth; the loose fields in the
            // global config are what the site used before it had author pages.
            $authorName = $articleAuthor
                ? (string) $articleAuthor->name
                : ($applicationGlobalOptions['authorName'][app()->getLocale()] ?? null);

            $authorJobTitle = $articleAuthor
                ? (string) $articleAuthor->job_title
                : ($applicationGlobalOptions['authorDescription'][app()->getLocale()] ?? null);

            $authorAvatar = $articleAuthor
                ? $articleAuthor->og_image_url
                : (($applicationGlobalOptions['authorAvatar'] ?? null)
                    ? url('/storage/' . $applicationGlobalOptions['authorAvatar'])
                    : null);

            $publisher = [
                '@type' => 'Organization',
                'name' => trans('base.organization'),
                'url' => url('/'),
            ];

            $articleSchema = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $articleUrl],
                'url' => $articleUrl,
                'headline' => (string) $blogArticle->name,
                'description' => $articleDescription,
                'image' => $blogArticle->og_image_url ? [$blogArticle->og_image_url] : null,
                'inLanguage' => app()->getLocale(),
                'datePublished' => $blogArticle->created_at?->toAtomString(),
                'dateModified' => $blogArticle->updated_at?->toAtomString(),
                'articleSection' => trans('base.blog'),
                'author' => $authorName ? array_filter([
                    '@type' => 'Person',
                    '@id' => $authorPageUrl ? url($authorPageUrl) . '#person' : null,
                    'name' => $authorName,
                    'jobTitle' => $authorJobTitle,
                    'image' => $authorAvatar,
                    'url' => $authorPageUrl ? url($authorPageUrl) : null,
                    'sameAs' => $articleAuthor?->sameAsLinks() ?: null,
                    'worksFor' => $publisher,
                ]) : null,
                'publisher' => $publisher,
            ]);

            $breadcrumbSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => trans('base.blog'), 'item' => url($blogUrl)],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => (string) $blogArticle->name, 'item' => $articleUrl],
                ],
            ];

            // Built as arrays and encoded rather than written out by hand: the
            // hand written version broke as soon as a value contained a quote
            // or a line break, and search engines silently dropped it.
            // Google stopped showing FAQ rich results in 2025, but Bing and the
            // AI crawlers still read this, and it costs nothing to be correct.
            $faqSchema = $articleFaq ? [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn ($entry) => [
                    '@type' => 'Question',
                    'name' => $entry['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $entry['answer'],
                    ],
                ], $articleFaq),
            ] : null;

            $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
        @endphp

        <script type="application/ld+json">{!! json_encode($articleSchema, $schemaFlags) !!}</script>
        <script type="application/ld+json">{!! json_encode($breadcrumbSchema, $schemaFlags) !!}</script>

        @if($faqSchema)
            <script type="application/ld+json">{!! json_encode($faqSchema, $schemaFlags) !!}</script>
        @endif

        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-md-offset-1">
                    <div class="blog-post">

                        <div class="blog-post-content">

                            <!-- === blog post title === -->
                            <div class="blog-post-title">
                                <h1 class="blog-title">
                                    {{ $blogArticle->name }}
                                </h1>
                                <div class="blog-post-date">
                                    <time datetime="{{ $blogArticle->created_at->toDateString() }}">
                                        {{ $blogArticle->created_at->translatedFormat('d F Y') }}
                                    </time>
                                </div>
                            </div>

                            <!-- === blog post text === -->
                            <div class="blog-post-text">

                                @foreach($blogArticle->blocks as $block)
                                    @if ($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_TEXT)

                                        {!! isset($block->content[app()->getLocale()]) ? $block->content[app()->getLocale()] : '' !!}

                                    @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_IMAGE)
                                        <div class="mx-auto">
                                            @foreach( $block->content['images'] as $image )
                                                <img src="{{ $image['image_url'] }}" alt="{{ $blogArticle->name }}">
                                            @endforeach
                                        </div>
                                    @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_VIDEO)
                                        <div class="mx-auto blog-video-wrapper">
                                            <div class="plyr__video-embed js-player">
                                                <iframe src="{{ $block->content['video_link'] }}" allowfullscreen allowtransparency allow="autoplay"></iframe>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach


                                <div class="art-post-author">

                                    <div class="author-avatar">
                                        @if($articleAuthor && $articleAuthor->photo_url)
                                            <a href="{{ $authorPageUrl }}">
                                                <img src="{{ $articleAuthor->photo_url }}" alt="{{ $authorName }}">
                                            </a>
                                        @elseif(array_key_exists('authorAvatar', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['authorAvatar']))
                                            <img src="{{ '/storage/' . $applicationGlobalOptions['authorAvatar'] }}" alt="{{ $authorName ?: 'Author avatar' }}">
                                        @endif
                                    </div>

                                    <div class="author-data">
                                        <span class="post-author-label">{{ trans('base.author') }}</span>

                                        @if($authorName)
                                            <span class="post-author-itself">
                                                @if($authorPageUrl)
                                                    <a href="{{ $authorPageUrl }}" rel="author">{{ $authorName }}</a>
                                                @else
                                                    {{ $authorName }}
                                                @endif
                                            </span>
                                        @endif

                                        @if($authorJobTitle)
                                            <span class="post-author-status">{{ $authorJobTitle }}</span>
                                        @endif

                                        @if($articleAuthor
                                            && $articleAuthor->short_description
                                            && trim((string) $articleAuthor->short_description) !== trim((string) $authorJobTitle))
                                            <span class="post-author-about">{{ $articleAuthor->short_description }}</span>
                                        @endif

                                        @if($authorPageUrl)
                                            <a class="post-author-more" href="{{ $authorPageUrl }}">{{ trans('base.author_read_more') }} &rarr;</a>
                                        @endif
                                    </div>

                                </div>

                                @php
                                    $shareUrl = url()->current();
                                @endphp

                                <div class="art-post-share">
                                    <span class="art-post-share__label">{{ trans('base.article_share') }}</span>

                                    <ul class="art-post-share__list">
                                        <li>
                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
                                               target="_blank" rel="noopener nofollow" aria-label="Facebook">
                                                <svg width="9" height="18" viewBox="0 0 9 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9 0.129807V2.98558H7.36458C6.76736 2.98558 6.36458 3.11538 6.15625 3.375C5.94792 3.63462 5.84375 4.02404 5.84375 4.54327V6.58774H8.89583L8.48958 9.78966H5.84375V18H2.65625V9.78966H0V6.58774H2.65625V4.22957C2.65625 2.88822 3.01736 1.84796 3.73958 1.10877C4.46181 0.369591 5.42361 0 6.625 0C7.64583 0 8.4375 0.0432688 9 0.129807Z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($blogArticle->name) }}"
                                               target="_blank" rel="noopener nofollow" aria-label="Telegram">
                                                <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.2632 14.951V14.9492L15.2795 14.9097L18 1.03268V0.988627C18 0.64262 17.873 0.340666 17.599 0.159861C17.3587 0.00108267 17.082 -0.00993075 16.8879 0.00475392C16.7072 0.0212889 16.5289 0.0582304 16.3563 0.114889C16.2827 0.138894 16.2101 0.165837 16.1386 0.195655L16.1268 0.200243L0.959514 6.22004L0.954979 6.22188C0.908866 6.23781 0.863732 6.2565 0.819816 6.27786C0.711948 6.3269 0.608454 6.38524 0.510484 6.45224C0.31545 6.58808 -0.055567 6.90747 0.0070251 7.41409C0.0587317 7.83444 0.344478 8.1006 0.537698 8.23919C0.651747 8.32005 0.774365 8.38777 0.903272 8.4411L0.9323 8.45395L0.941372 8.4567L0.947722 8.45946L3.60199 9.36348C3.59292 9.53144 3.60925 9.70306 3.6537 9.87286L4.98265 14.9749C5.05525 15.253 5.21212 15.5011 5.43124 15.6844C5.65037 15.8676 5.92072 15.9769 6.20435 15.9967C6.48798 16.0166 6.77063 15.946 7.0126 15.795C7.25457 15.644 7.44371 15.4201 7.55346 15.1547L9.62898 12.9098L13.1931 15.6742L13.2439 15.6962C13.5677 15.8394 13.8698 15.8844 14.1465 15.8468C14.4232 15.8082 14.6427 15.6907 14.8078 15.5577C14.9988 15.401 15.151 15.2015 15.2523 14.9749L15.2596 14.9593L15.2623 14.9538L15.2632 14.951ZM4.96904 9.52226C4.95432 9.46565 4.95782 9.40577 4.97903 9.3513C5.00023 9.29684 5.03804 9.25063 5.08697 9.21939L14.0866 3.4373C14.0866 3.4373 14.6164 3.11148 14.5973 3.4373C14.5973 3.4373 14.6917 3.4942 14.4078 3.76128C14.1392 4.01551 7.99342 10.0188 7.37113 10.6264C7.33653 10.6604 7.31228 10.7037 7.30128 10.7512L6.29799 14.6243L4.96904 9.52226Z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="viber://forward?text={{ urlencode($blogArticle->name . ' ' . $shareUrl) }}"
                                               rel="noopener nofollow" aria-label="Viber">
                                                <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8201 0.463759C9.97485 -0.154586 7.0243 -0.154586 4.17908 0.463759L3.87251 0.529701C3.07218 0.703667 2.33713 1.08993 1.74899 1.64559C1.16086 2.20125 0.742685 2.90454 0.540912 3.67735C-0.180304 6.44036 -0.180304 9.33474 0.540912 12.0977C0.733334 12.8348 1.12277 13.5092 1.66974 14.0527C2.21671 14.5963 2.90177 14.9895 3.65547 15.1926L4.07599 17.6334C4.08941 17.7109 4.12396 17.7835 4.17604 17.8436C4.22811 17.9037 4.29582 17.9491 4.37209 17.9752C4.44837 18.0012 4.53042 18.0069 4.60972 17.9916C4.68902 17.9764 4.76266 17.9407 4.82297 17.8884L7.29273 15.7404C9.14672 15.8504 11.0074 15.7063 12.8201 15.3122L13.1275 15.2463C13.9279 15.0723 14.6629 14.686 15.2511 14.1304C15.8392 13.5747 16.2574 12.8714 16.4591 12.0986C17.1803 9.33561 17.1803 6.44124 16.4591 3.67823C16.2573 2.90531 15.839 2.20194 15.2507 1.64627C14.6624 1.0906 13.9271 0.704401 13.1266 0.53058L12.8201 0.463759ZM4.94235 3.60613C4.77427 3.58235 4.60289 3.61524 4.45671 3.69933H4.44405C4.10493 3.89276 3.79926 4.13631 3.53881 4.42294C3.32176 4.66648 3.2042 4.91267 3.17345 5.15006C3.15537 5.29074 3.16803 5.43317 3.21053 5.5677L3.22681 5.57649C3.47098 6.27372 3.78931 6.94457 4.17818 7.57586C4.67959 8.46256 5.29663 9.28262 6.01399 10.0157L6.0357 10.0456L6.07006 10.0702L6.09086 10.094L6.11618 10.1151C6.87287 10.8146 7.71833 11.4174 8.63206 11.9087C9.67658 12.4617 10.3105 12.7229 10.6912 12.8319V12.8372C10.8025 12.8706 10.9038 12.8855 11.006 12.8855C11.3302 12.8622 11.6371 12.7341 11.8777 12.5215C12.1717 12.2683 12.4204 11.9703 12.6139 11.6388V11.6326C12.7957 11.2985 12.7342 10.9838 12.4719 10.7701C11.9451 10.3226 11.3755 9.92514 10.7708 9.58314C10.3657 9.36949 9.95421 9.49874 9.78781 9.71503L9.43241 10.1511C9.24973 10.3674 8.91874 10.3375 8.91874 10.3375L8.9097 10.3428C6.43994 9.72998 5.78067 7.2989 5.78067 7.2989C5.78067 7.2989 5.74992 6.96831 5.97872 6.7995L6.42366 6.45132C6.63708 6.28251 6.7854 5.88334 6.5566 5.48944C6.20716 4.90103 5.79916 4.34739 5.33845 3.83649C5.23796 3.71625 5.09705 3.63436 4.94054 3.60525L4.94235 3.60613ZM9.11498 2.5493C8.99506 2.5493 8.88005 2.59561 8.79525 2.67806C8.71045 2.7605 8.66281 2.87232 8.66281 2.98891C8.66281 3.1055 8.71045 3.21732 8.79525 3.29977C8.88005 3.38221 8.99506 3.42853 9.11498 3.42853C10.259 3.42853 11.2085 3.79165 11.96 4.488C12.3462 4.86871 12.6473 5.31975 12.8445 5.81388C13.0425 6.30889 13.133 6.83731 13.1095 7.3666C13.107 7.42433 13.1162 7.48198 13.1366 7.53624C13.157 7.59051 13.1882 7.64033 13.2285 7.68287C13.3097 7.76877 13.4227 7.81979 13.5426 7.82468C13.6626 7.82958 13.7796 7.78796 13.8679 7.70898C13.9563 7.63 14.0088 7.52012 14.0138 7.40353C14.0419 6.75245 13.9307 6.10278 13.6873 5.4956C13.4429 4.88553 13.0723 4.33072 12.5985 3.8655L12.5895 3.85671C11.6571 2.99067 10.4769 2.5493 9.11498 2.5493ZM9.08423 3.99475C8.96431 3.99475 8.8493 4.04107 8.7645 4.12351C8.6797 4.20596 8.63206 4.31777 8.63206 4.43437C8.63206 4.55096 8.6797 4.66278 8.7645 4.74522C8.8493 4.82767 8.96431 4.87398 9.08423 4.87398H9.09961C9.92437 4.93113 10.5249 5.19842 10.9454 5.63716C11.3767 6.08908 11.6001 6.65091 11.5829 7.3455C11.5802 7.46209 11.6252 7.57498 11.708 7.65932C11.7909 7.74366 11.9048 7.79255 12.0247 7.79523C12.1446 7.79791 12.2607 7.75416 12.3475 7.67362C12.4342 7.59307 12.4845 7.48232 12.4873 7.36572C12.509 6.45396 12.2069 5.66705 11.6083 5.03928V5.03752C10.996 4.3992 10.1559 4.06157 9.14483 3.99563L9.12945 3.99387L9.08423 3.99475ZM9.06705 5.46746C9.00654 5.46227 8.94557 5.46899 8.88779 5.48721C8.83 5.50544 8.77659 5.53479 8.73073 5.57353C8.68488 5.61227 8.64752 5.65959 8.62089 5.71268C8.59426 5.76576 8.57891 5.82352 8.57575 5.8825C8.57259 5.94148 8.58168 6.00048 8.60249 6.05597C8.6233 6.11146 8.6554 6.1623 8.69687 6.20546C8.73833 6.24862 8.78832 6.28322 8.84384 6.30718C8.89936 6.33114 8.95929 6.34399 9.02003 6.34494C9.39804 6.36428 9.6395 6.47506 9.79143 6.62365C9.94426 6.77312 10.0582 7.01315 10.079 7.38858C10.0801 7.44758 10.0935 7.50575 10.1182 7.55963C10.143 7.61351 10.1786 7.662 10.223 7.7022C10.2675 7.7424 10.3197 7.7735 10.3768 7.79363C10.4338 7.81377 10.4945 7.82253 10.5551 7.81941C10.6157 7.81628 10.675 7.80132 10.7296 7.77542C10.7841 7.74952 10.8327 7.71322 10.8725 7.66867C10.9123 7.62411 10.9425 7.57223 10.9612 7.51611C10.9799 7.45998 10.9868 7.40076 10.9815 7.34198C10.9526 6.81445 10.7826 6.34845 10.4344 6.00555C10.0844 5.66265 9.60785 5.4956 9.06705 5.46746Z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button"
                                                    class="art-post-share__copy js-article-share-copy"
                                                    data-url="{{ $shareUrl }}"
                                                    data-copied-text="{{ trans('base.article_link_copied') }}"
                                                    title="{{ trans('base.article_copy_link') }}"
                                                    aria-label="{{ trans('base.article_copy_link') }}">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                            </div>


                        </div>


                    </div><!--blog-post-->
                </div><!--col-sm-8-->
            </div> <!--/row-->
        </div><!--/container-->
    </section>

    <section class="blog art-single-latest-articles">

        <div class="container">

            <div class="row">
                <header class="col-12 art-header-left">
                    <div>
                        <h2 class="title">{{trans('base.article_read_also')}}</h2>
                    </div>
                </header>
            </div>

            <div class="row">
                <div class="art-blog-archive-wrapper">
                    @foreach($latestArticles as $latestArticle)
                        <div class="col-lg-4">
                            @include('pages.store.partials.article_item', ['article' => $latestArticle])
                        </div>
                    @endforeach
                </div>
            </div> <!--/row-->

        </div>

    </section>



@endsection

@push('head')
    <style>
        /* The blog hero is styled by .main-header.main-header-blog, whose
           padding-top of 650px would push the crumbs into the middle of the
           picture. They are lifted out of the flow instead, so they land right
           under the site header, level with where they sit on every other page
           (.main-header padding-top 145px + .art-page-header margin-top 14px). */
        .main-header.main-header-blog {
            position: relative;
        }

        .main-header.main-header-blog > header {
            position: absolute;
            top: 159px;
            left: 0;
            right: 0;
            margin-bottom: 0;
            z-index: 2;
        }

        @media (max-width: 991px) {
            .main-header.main-header-blog > header {
                top: 14px;
            }
        }

        /* A light scrim over the cover, keeping the crumbs readable whatever
           the photo happens to be. */
        .main-header.main-header-blog:before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, .2);
            pointer-events: none;
        }

        /* Readability comes from the scrim over the cover, so the crumbs need
           no text shadow of their own. */
        .main-header-blog .art-article-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin: 0;
            padding: 0;
            background: none;
        }

        .main-header-blog .art-article-breadcrumb > li {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            font-size: 13px;
            color: #ffffff;
        }

        .main-header-blog .art-article-breadcrumb > li + li:before {
            content: "/";
            padding: 0 8px;
            opacity: .6;
        }

        .main-header-blog .art-article-breadcrumb > li a {
            color: #ffffff;
            opacity: .85;
        }

        .main-header-blog .art-article-breadcrumb > li a:hover {
            opacity: 1;
            text-decoration: underline;
        }

        .main-header-blog .art-article-breadcrumb > li .icon-home {
            margin-right: 6px;
        }

        .main-header-blog .art-article-breadcrumb > li .active {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            opacity: .75;
        }

        @media (max-width: 767px) {
            .main-header-blog .art-article-breadcrumb > li .active {
                max-width: 100%;
            }
        }

        /* The FAQ block reuses the site accordion, so it only needs the few
           resets a heading brings along that a <button> did not. */
        .blog-post .article-faq {
            margin-top: 35px;
        }

        .blog-post .article-faq > h2 {
            margin-bottom: 18px;
        }

        /* The theme gives every heading inside an article margin-top: 35px
           through .blog .blog-post .blog-post-text h3, which outweighs a
           shorter selector and left a gap above every question. */
        .blog .blog-post .blog-post-text .article-faq .accordion {
            margin: 0;
            font-weight: 500;
            line-height: 1.35;
        }

        /*
            Article body: tables, lists and quotes arrive as plain HTML from
            Serp Agent, so the theme styles them here rather than in the
            editor.
        */
        .blog .blog-post .blog-post-text .article-table {
            margin: 25px 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .blog .blog-post .blog-post-text table {
            width: 100%;
            /* Narrower than this a table stops being readable, so it scrolls
               inside its wrapper instead of squeezing the columns. */
            min-width: 520px;
            margin: 0;
            border-collapse: collapse;
            font-size: 15px;
            font-weight: 300;
        }

        .blog .blog-post .blog-post-text table th,
        .blog .blog-post .blog-post-text table td {
            padding: 12px 14px;
            border: 1px solid #dddddd;
            text-align: left;
            vertical-align: top;
        }

        .blog .blog-post .blog-post-text table th {
            background-color: #f5f5f5;
            font-weight: 500;
        }

        .blog .blog-post .blog-post-text table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* Scoped away from the share row, whose list carries icons rather
           than prose and must keep its own styling. */
        .blog .blog-post .blog-post-text ul:not(.art-post-share__list),
        .blog .blog-post .blog-post-text ol {
            margin: 0 0 20px;
            padding-left: 22px;
        }

        .blog .blog-post .blog-post-text ul:not(.art-post-share__list) {
            list-style: disc;
        }

        .blog .blog-post .blog-post-text ol {
            list-style: decimal;
        }

        .blog .blog-post .blog-post-text ul:not(.art-post-share__list) > li,
        .blog .blog-post .blog-post-text ol > li {
            margin-bottom: 8px;
            font-weight: 300;
        }

        .blog .blog-post .blog-post-text blockquote {
            margin: 25px 0;
            padding: 6px 0 6px 20px;
            border-left: 3px solid #333333;
            font-weight: 300;
            font-style: italic;
        }

        .blog .blog-post .blog-post-text blockquote > *:last-child {
            margin-bottom: 0;
        }

        /* The theme stretches every article image to 100%; without this the
           proportions go with it. */
        .blog .blog-post .blog-post-text img {
            height: auto;
        }

        @media (max-width: 767px) {
            .blog .blog-post .blog-post-text table {
                min-width: 440px;
                font-size: 14px;
            }

            .blog .blog-post .blog-post-text table th,
            .blog .blog-post .blog-post-text table td {
                padding: 10px;
            }
        }

        .blog-post .article-faq .art-panel .panel-data > *:last-child {
            margin-bottom: 0;
        }

        .blog-post .blog-post-date {
            margin-top: 12px;
            font-size: 14px;
            font-weight: 300;
            color: #777777;
        }

        .blog-post .art-post-author .post-author-itself a {
            color: inherit;
        }

        .blog-post .art-post-author .post-author-itself a:hover {
            text-decoration: underline;
        }

        .blog-post .art-post-author .post-author-about {
            font-weight: 300;
            font-size: 13px;
            margin-top: 6px;
        }

        .blog-post .art-post-author .post-author-more {
            display: inline-block;
            margin-top: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        .blog-post .art-post-share {
            border-top: 1px solid #dddddd;
            padding-top: 25px;
            margin-top: 35px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .blog-post .art-post-share__label {
            font-size: 14px;
            font-weight: 500;
            margin-right: 18px;
        }

        .blog-post .art-post-share__list {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .blog-post .art-post-share__list li {
            margin: 0 10px 0 0;
        }

        .blog-post .art-post-share__list a,
        .blog-post .art-post-share__copy {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dddddd;
            border-radius: 100%;
            background: none;
            padding: 0;
            color: #333333;
            transition: background-color .2s ease, border-color .2s ease, color .2s ease;
        }

        .blog-post .art-post-share__copy {
            cursor: pointer;
        }

        .blog-post .art-post-share__list a:hover,
        .blog-post .art-post-share__copy:hover,
        .blog-post .art-post-share__copy.is-copied {
            background-color: #333333;
            border-color: #333333;
            color: #ffffff;
        }
    </style>
@endpush

@push('dynamic_scripts')
    <script>
        // Tables get a scrolling wrapper so a wide one does not drag the
        // whole page sideways on a phone. New articles arrive already
        // wrapped; this covers everything published before that.
        document.querySelectorAll('.blog-post-text table').forEach(function (table) {
            var parent = table.parentElement;

            if (parent && parent.classList.contains('article-table')) {
                return;
            }

            var wrapper = document.createElement('div');
            wrapper.className = 'article-table';

            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });

        // The accordion trigger is a heading, so it needs the keyboard
        // behaviour a button would have given for free.
        document.querySelectorAll('.article-faq .accordion[tabindex]').forEach(function (heading) {
            heading.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                    event.preventDefault();
                    heading.click();
                }
            });
        });

        document.querySelectorAll('.js-article-share-copy').forEach(function (button) {
            button.addEventListener('click', function () {
                var url = button.getAttribute('data-url');

                var markAsCopied = function () {
                    button.classList.add('is-copied');
                    button.setAttribute('title', button.getAttribute('data-copied-text'));

                    setTimeout(function () {
                        button.classList.remove('is-copied');
                    }, 2000);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(markAsCopied);

                    return;
                }

                // Fallback for browsers without the async clipboard API.
                var input = document.createElement('input');
                input.value = url;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                markAsCopied();
            });
        });
    </script>
@endpush
