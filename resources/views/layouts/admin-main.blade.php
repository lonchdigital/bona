<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex">
    <title>{{ config('app.name') }} - {{ trans('admin.admin_panel') }}</title>
    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="/static-admin/css/simplebar.css">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="/static-admin/css/custom.css">
    <link rel="stylesheet" href="/static-admin/css/feather.css">
    <link rel="stylesheet" href="/static-admin/css/select2.css">
    <link rel="stylesheet" href="/static-admin/css/uppy.min.css">
    <link rel="stylesheet" href="/static-admin/css/jquery.steps.css">
    <link rel="stylesheet" href="/static-admin/css/jquery.timepicker.css">
    <link rel="stylesheet" href="/static-admin/css/quill.snow.css">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="/static-admin/css/daterangepicker.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="/static-admin/css/app-light.css" id="lightTheme">
    <link rel="stylesheet" href="/static-admin/css/dataTables.bootstrap4.css">

    <link rel="stylesheet" href="/static-admin/css/vue-multiselect.css">

    <!-- DROP ZONE -->
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
</head>
<body class="vertical  light  ">
<div class="wrapper" id="app">
    <nav class="topnav navbar navbar-light d-flex justify-content-between">
        <ul class="nav">
            <li class="nav-item nav-notif">
                <a href="#" id="admin-side-menu-toggle" class="nav-link text-muted my-2">
                    <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 18L20 18" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <path d="M4 12L20 12" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <path d="M4 6L20 6" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </a>
            </li>
        </ul>
        <ul class="nav">
            @foreach(app()->make(\App\Services\Application\ApplicationConfigService::class)->getAvailableLanguages() as $availableLanguage)
                @if (mb_strtoupper($availableLanguage) !== mb_strtoupper(app()->getLocale()))
                    <li class="nav-item nav-notif">
                        <a class="nav-link text-muted my-2" href="{{ route('locale.change', ['newLocale' => mb_strtolower($availableLanguage)]) }}">
                            {{ mb_strtoupper($availableLanguage) }}
                        </a>
                    </li>
                @else
                    <li class="nav-item nav-notif">
                        <a class="nav-link text-muted my-2" href="#">
                            <strong>{{ mb_strtoupper($availableLanguage) }}</strong>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
    <aside class="sidebar-left border-right bg-white shadow" id="leftSidebar" data-simplebar>
        <a href="#" class="btn toggle-btn d-lg-none text-muted ml-2 mt-3" id="adminCloseSideMenu">
            <i class="fe fe-x"><span class="sr-only"></span></i>
        </a>
        <nav class="vertnav navbar navbar-light">
            <!-- nav bar -->
            <div class="w-100 mb-4 d-flex">
                <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="{{ route('admin.dashboard.page') }}">
                    <img class="main-logo-admin" src="{{ asset('storage/logo/logo-dark.jpg') }}" alt="logo">
                </a>
            </div>
{{--
            <ul class="navbar-nav flex-fill w-100 mb-2">
                <li class="nav-item w-100">
                    <a class="nav-link" href="{{ route('admin.dashboard.page') }}">
                        <i class="fe fe-home fe-16"></i>
                        <span class="ml-3 item-text">{{ trans('admin.dashboard') }}</span><span class="sr-only">(current)</span>
                    </a>
                </li>
            </ul>
            --}}

            <ul class="navbar-nav flex-fill w-100 mb-2">
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.order.list.page') }}">
                        <i class="fe fe-list fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.orders_list') }}</span>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav flex-fill w-100 mb-2">
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.visit-request.list.page') }}">
                        <i class="fe fe-list fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.visit_requests_list') }}</span>
                    </a>
                </li>
            </ul>

            <p class="text-muted nav-heading mt-4 mb-1">
                <span>{{ trans('admin.products') }}</span>
            </p>
            <ul class="navbar-nav flex-fill w-100 mb-2">
                @foreach($productTypes as $productType)
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('admin.product.list.page', ['productType' => $productType->id]) }}">
                            <i class="fe fe-layers fe-16"></i>
                            <span class="ml-3 item-text">{{ $productType->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>


            <p class="text-muted nav-heading mt-4 mb-1">
                <span>{{ trans('admin.customization') }}</span>
            </p>
            <ul class="navbar-nav flex-fill w-100 mb-2">
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.pages.list.page') }}">
                        <i class="fe fe-list fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.pages') }}</span>
                    </a>
                </li>
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.product-type.list.page') }}">
                        <i class="fe fe-codesandbox fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.product_types') }}</span>
                    </a>
                </li>
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.catalog-menu.page') }}">
                        <i class="fe fe-menu fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.catalog_menu') }}</span>
                    </a>
                </li>
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.product-field.list.page') }}">
                        <i class="fe fe-codepen fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.product_fields') }}</span>
                    </a>
                </li>
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.product-attribute.list.page') }}">
                        <i class="fe fe-codepen fe-16"></i>
                        <span class="ml-1 item-text">Атрибуты товаров</span>
                    </a>
                </li>
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.product-category.list-by-product-type.page') }}">
                        <i class="fe fe-grid fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.product_categories') }}</span>
                    </a>
                </li>
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.currency.list.page') }}">
                        <i class="fe fe-dollar-sign fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.currencies') }}</span>
                    </a>
                </li>
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.brand.list.page') }}">
                        <i class="fe fe-hexagon fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.brands') }}</span>
                    </a>
                </li>
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.color.list.page') }}">
                        <i class="fe fe-droplet fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.colors') }}</span>
                    </a>
                </li>
<!--                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.country.list.page') }}">
                        <i class="fe fe-flag fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.countries') }}</span>
                    </a>
                </li>-->
                <li class="nav-item w-100">
                    <a class="nav-link pl-3" href="{{ route('admin.application-config.edit.page') }}">
                        <i class="fe fe-list fe-16"></i>
                        <span class="ml-1 item-text">{{ trans('admin.application_config') }}</span>
                    </a>
                </li>
            </ul>

                                    <p class="text-muted nav-heading mt-4 mb-1">
                            <span>{{ trans('admin.our_works') }}</span>
                        </p>
                        <ul class="navbar-nav flex-fill w-100 mb-2">
                            <li class="nav-item w-100">
                                <a class="nav-link pl-3" href="{{ route('admin.work.list.page') }}">
                                    <i class="fe fe-list fe-16"></i>
                                    <span class="ml-1 item-text">{{ trans('admin.our_works') }}</span>
                                </a>
                            </li>
                        </ul>
                        

            {{--
                <p class="text-muted nav-heading mt-4 mb-1">
                    <span>{{ trans('admin.products_import') }}</span>
                </p>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item dropdown">
                        <a href="#products-import" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
                            <i class="fe fe-book fe-16"></i>
                            <span class="ml-3 item-text">{{ trans('admin.products_import') }}</span>
                        </a>
                        <ul class="collapse list-unstyled pl-4 w-100" id="products-import">
                            @foreach($productTypes as $productType)
                                <a class="nav-link pl-3" href="{{ route('admin.products-import.page', ['productType' => $productType->id]) }}"><span class="ml-1">{{ $productType->name }}</span></a>
                            @endforeach
                        </ul>
                    </li>
                </ul>
                --}}


<p class="text-muted nav-heading mt-4 mb-1">
    <span>{{ trans('admin.blog') }}</span>
</p>
<ul class="navbar-nav flex-fill w-100 mb-2">
    <li class="nav-item w-100">
        <a class="nav-link pl-3" href="{{ route('admin.blog-article.list.page') }}">
            <i class="fe fe-align-justify fe-16"></i>
            <span class="ml-1 item-text">{{ trans('admin.blog_articles') }}</span>
        </a>
    </li>
    <li class="nav-item w-100">
        <a class="nav-link pl-3" href="{{ route('admin.product-review.list.page') }}">
            <i class="fe fe-star fe-16"></i>
            <span class="ml-1 item-text">{{ trans('admin.product_reviews') }}</span>
        </a>
    </li>
    <li class="nav-item w-100">
        <a class="nav-link pl-3" href="{{ route('admin.author.list.page') }}">
            <i class="fe fe-user fe-16"></i>
            <span class="ml-1 item-text">{{ trans('admin.authors') }}</span>
        </a>
    </li>
</ul>


<p class="text-muted nav-heading mt-4 mb-1">
    <span>{{ trans('admin.seo') }}</span>
</p>
<ul class="navbar-nav flex-fill w-100 mb-2">
    <li class="nav-item dropdown">
        <a href="#seo" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
            <i class="fe fe-book fe-16"></i>
            <span class="ml-3 item-text">{{ trans('admin.seo') }}</span>
        </a>
        <ul class="collapse list-unstyled pl-4 w-100" id="seo">
            <a class="nav-link pl-3" href="{{ route('admin.seo.robots-txt.edit.page') }}"><span class="ml-1">{{ trans('admin.robots_txt') }}</span></a>
        </ul>
        <ul class="collapse list-unstyled pl-4 w-100" id="seo">
            <a class="nav-link pl-3" href="{{ route('admin.filter-groups.list.page') }}"><span class="ml-1">{{ trans('admin.filter_groups') }}</span></a>
        </ul>
    </li>
    <li class="nav-item w-100 mt-5 mb-3">
        <form method="POST" action="{{ route('admin.log-out') }}">
            @csrf
            <button type="submit" class="nav-link pl-3 border-0 bg-transparent">
                <i class="fe fe-corner-up-left fe-16"></i>
                <span class="ml-1 item-text">{{ trans('auth.logout') }}</span>
            </button>
        </form>
    </li>
</ul>
</nav>
</aside>
<main role="main" class="main-content">
@yield('content')
<div class="modal fade modal-notif modal-slide" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="defaultModalLabel">Notifications</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="list-group list-group-flush my-n3">
                <div class="list-group-item bg-transparent">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="fe fe-box fe-24"></span>
                        </div>
                        <div class="col">
                            <small><strong>Package has uploaded successfull</strong></small>
                            <div class="my-0 text-muted small">Package is zipped and uploaded</div>
                            <small class="badge badge-pill badge-light text-muted">1m ago</small>
                        </div>
                    </div>
                </div>
                <div class="list-group-item bg-transparent">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="fe fe-download fe-24"></span>
                        </div>
                        <div class="col">
                            <small><strong>Widgets are updated successfull</strong></small>
                            <div class="my-0 text-muted small">Just create new layout Index, form, table</div>
                            <small class="badge badge-pill badge-light text-muted">2m ago</small>
                        </div>
                    </div>
                </div>
                <div class="list-group-item bg-transparent">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="fe fe-inbox fe-24"></span>
                        </div>
                        <div class="col">
                            <small><strong>Notifications have been sent</strong></small>
                            <div class="my-0 text-muted small">Fusce dapibus, tellus ac cursus commodo</div>
                            <small class="badge badge-pill badge-light text-muted">30m ago</small>
                        </div>
                    </div> <!-- / .row -->
                </div>
                <div class="list-group-item bg-transparent">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="fe fe-link fe-24"></span>
                        </div>
                        <div class="col">
                            <small><strong>Link was attached to menu</strong></small>
                            <div class="my-0 text-muted small">New layout has been attached to the menu</div>
                            <small class="badge badge-pill badge-light text-muted">1h ago</small>
                        </div>
                    </div>
                </div> <!-- / .row -->
            </div> <!-- / .list-group -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">Clear All</button>
        </div>
    </div>
</div>
</div>
</main> <!-- main -->
</div> <!-- .wrapper -->
@hasSection('vue')
@vite('resources/js/admin/app.js')
@vite('resources/js/admin/scripts.js')
@else
<script src="/static-admin/js/tinycolor-min.js"></script>
<script src="/static-admin/js/config.js"></script>
<script src="/static-admin/js/jquery.min.js"></script>
<script src='/static-admin/js/select2.full.min.js'></script>
<script src='/static-admin/js/jquery.mask.min.js'></script>
<script src="/static-admin/js/popper.min.js"></script>
<script src="/static-admin/js/bootstrap.min.js"></script>
<script src="/static-admin/js/scripts.js"></script>
@endif
{{--<script src="/static-admin/js/moment.min.js"></script>--}}
{{--<script src="/static-admin/js/simplebar.min.js"></script>--}}
{{--<script src='/static-admin/js/jquery.stickOnScroll.js'></script>--}}

{{--<script src="/static-admin/js/topojson.min.js"></script>--}}
{{--<script src="/static-admin/js/jquery.sparkline.min.js"></script>--}}
{{--<script src='/static-admin/js/jquery.steps.min.js'></script>--}}
{{--<script src='/static-admin/js/jquery.validate.min.js'></script>--}}
{{--<script src='/static-admin/js/jquery.timepicker.js'></script>--}}
{{--<script src='/static-admin/js/dropzone.min.js'></script>--}}
{{--<script src="/static-admin/js/apps.js"></script>--}}
@stack('scripts')

{{--
    Saving an edit no longer throws you back to the list, so the confirmation
    has to come to you. Deliberately plain: the panel loads several script
    bundles and none of them agree on a notification widget.
--}}
<div id="admin-toasts" aria-live="polite" aria-atomic="true"
     style="position: fixed; right: 24px; bottom: 24px; z-index: 2000; display: flex; flex-direction: column; gap: 10px;"></div>

<script>
    window.adminToast = function (message, isSuccess) {
        if (!message) {
            return;
        }

        var host = document.getElementById('admin-toasts');

        if (!host) {
            return;
        }

        var toast = document.createElement('div');
        toast.setAttribute('role', 'status');
        toast.textContent = message;
        toast.style.cssText = [
            'min-width: 240px',
            'max-width: 420px',
            'padding: 12px 18px',
            'border-radius: 6px',
            'color: #fff',
            'font-size: 14px',
            'line-height: 1.4',
            'box-shadow: 0 6px 18px rgba(0, 0, 0, .18)',
            'opacity: 0',
            'transform: translateY(8px)',
            'transition: opacity .2s ease-out, transform .2s ease-out',
            'background: ' + (isSuccess === false ? '#dc3545' : '#28a745'),
        ].join(';');

        host.appendChild(toast);

        window.requestAnimationFrame(function () {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        window.setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
            window.setTimeout(function () {
                toast.remove();
            }, 200);
        }, 3500);
    };
</script>
</body>
</html>
