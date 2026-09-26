@extends('layouts.admin-main')
@section('content')<div class="container-fluid"><h1 class="h3">Примірка ще не готова</h1><p>Виправте ці поля в редакторі та збережіть чернетку:</p><ul>@foreach($issues as $issue)<li>{{ $issue }}</li>@endforeach</ul><p>Цей перегляд доступний лише адміністратору.</p></div>@endsection
