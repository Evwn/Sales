@extends('errors.layout')

@section('title', 'Forbidden')
@section('code', '403')
@section('message', 'You don’t have permission to view this page.')
@section('action')
    <a href="{{ url('/') }}">Go Back</a>
@endsection
