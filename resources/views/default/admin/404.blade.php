<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
    <title>{{ config('app.name') }} - {{ trans('admin.admin_panel') }}</title>
    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="/static-admin/css/simplebar.css">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="/static-admin/css/feather.css">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="/static-admin/css/daterangepicker.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="/static-admin/css/app-light.css" id="lightTheme">
</head>
<body class="light ">
<div class="wrapper vh-100">
    <div class="align-items-center h-100 d-flex w-50 mx-auto">
        <div class="mx-auto text-center">
            <h1 class="display-1 m-0 font-weight-bolder text-muted" style="font-size:80px;">404</h1>
            <h1 class="mb-1 text-muted font-weight-bold">{{ trans('admin.oops') }}</h1>
            <h6 class="mb-3 text-muted">{{ trans('admin.page_404') }}</h6>
            <a href="{{ route('admin.dashboard.page') }}" class="btn btn-lg btn-primary px-5">{{ trans('admin.go_to_main') }}</a>
        </div>
    </div>
</div>
</body>
</html>
</body>
</html>
