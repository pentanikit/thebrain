<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="">
    <meta property="og:type" content="">
    <meta property="og:url" content="">
    <meta property="og:image" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="assets/imgs/theme/favicon.svg">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <title>Ecom - Marketplace Dashboard Template</title>
    
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


</head>

<body>

    <div class="screen-overlay"></div>
    <x-backend.sidebar />

    @yield('admin')

    <script src="{{ asset('assets/js/vendors/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/jquery.fullscreen.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/chart.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/custom-chart.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @stack('scripts')
</body>

</html>
