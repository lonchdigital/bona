@extends('layouts.email-main')

@section('content')

<h1>{{ $title }}</h1>
<p><b>Product:</b> <a href="{{ $current_product_url }}">{{ $current_product_title }}</a></p>
<p><b>Name:</b> {{ $name }}</p>
<p><b>Phone:</b> {{ $phone }}</p>

@endsection
