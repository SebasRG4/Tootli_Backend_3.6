@extends('errors::minimal')

@section('title', 'Prohibido')
@section('code', '403')
@section('message', translate($exception->getMessage() ?: 'Forbidden'))
