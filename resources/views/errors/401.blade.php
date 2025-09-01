@extends('errors.layout')

@section('title', 'Unauthorized')
@section('code', '401')
@section('message', 'You are not authorized to access this page.')
@section('action')
    <a href="{{ url('/') }}">Go Home</a>
@endsection
