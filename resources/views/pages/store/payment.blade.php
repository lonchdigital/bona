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

                let statusId;
                switch(data.status) {
                    case 'success':
                        statusId = 1;
                        break;
                    case 'error':
                        statusId = 5;
                        break;
                    case 'failure':
                        statusId = 5;
                        break;
                    default:
                        statusId = 5;
                        break;
                }


                let routeWithOrderId = '{{ route('payment.update-payment-status', ['order' => 'ORDER_ID']) }}';
                routeWithOrderId = routeWithOrderId.replace('ORDER_ID', data.order_id);
                $.ajax({
                    url: routeWithOrderId,
                    type: 'get',
                    data: {
                        _token: '{{ csrf_token() }}',
                        statusId: statusId
                    },
                    dataType: 'json',
                }).done(function() {
                    console.log('success');
                }).fail(function () {
                    console.error('error');
                });

            }).on("liqpay.ready", function(data){
                // ready
            }).on("liqpay.close", function(data){
                // close
            });
        };
    </script>
    <script src="//static.liqpay.ua/libjs/checkout.js" async></script>
    <script src="/static-admin/js/jquery.min.js" async></script>
@stop
