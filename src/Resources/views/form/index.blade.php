@extends('marble::layouts.app')

@section('content')
    <h1>{{ $item->name() }}</h1>

    @include('marble::form.submissions-table', ['item' => $item, 'submissions' => $submissions])
@endsection
