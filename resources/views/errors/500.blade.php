@extends('errors.layout')

@section('title', 'Server Error')
@section('code', '500')
@section('message', 'Something went wrong on our end. Please try again later.')
@section('action')
    <a href="{{ url('/') }}">Go Home</a>
@endsection
