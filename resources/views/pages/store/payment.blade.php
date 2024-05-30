@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')

    @include('pages.store.partials.page_header_empty')


    <div id="liqpay_checkout"></div>
    <script>
        window.LiqPayCheckoutCallback = function() {
            LiqPayCheckout.init({
                data: "{{ $data }}",
                signature: "{{ $signature }}",
                embedTo: "#liqpay_checkout",
                mode: "embed" // embed || popup
            }).on("liqpay.callback", function(data){
                console.log(data.status);
                console.log(data);
            }).on("liqpay.ready", function(data){
// ready
            }).on("liqpay.close", function(data){
// close
            });
        };
    </script>
    <script src="//static.liqpay.ua/libjs/checkout.js" async></script>
@stop
