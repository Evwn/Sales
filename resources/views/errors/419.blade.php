@extends('errors.layout')

@section('title', 'Page Expired')
@section('code', '419')
@section('message', 'Your session has expired or the CSRF token is invalid.')
@section('action')
    <form method="GET" action="{{ url()->current() }}">
        <button type="submit">Reload Page</button>
    </form>
@endsection
