@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . $heading }}</title>
@endsection

@section('content')
    <main class="main">
        <div class="content">
            <div class="entry-content">
                <div class="container">
                    <h1>{{ $heading }}</h1>
                    {!! $content !!}
                </div>
            </div>
        </div>
    </main>
@endsection
