@props(['event', 'payload' => [], 'once' => null])
{{-- Read by resources/js/store/common/analytics.js after cookie consent. --}}
<script type="application/json" data-ga-event="{{ $event }}"@if($once) data-ga-once="{{ $once }}"@endif>{!! json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
