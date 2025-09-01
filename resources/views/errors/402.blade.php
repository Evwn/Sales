@extends('errors.layout')

@section('title', 'Payment Required')
@section('code', '402')
@section('message', 'Access to this resource requires payment.')
@section('action')
    <a href="{{ url('/') }}">Return</a>
@endsection
