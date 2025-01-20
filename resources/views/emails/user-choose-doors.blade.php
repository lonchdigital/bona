@extends('layouts.email-main')

@section('content')

<h1>{{ $title }}</h1>
<p><b>Name:</b> {{ $name }}</p>
<p><b>Phone:</b> {{ $phone }}</p>
<p><b>Message:</b> {{ $description }}</p>

@endsection
