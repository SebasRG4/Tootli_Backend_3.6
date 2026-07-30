@extends('errors::minimal')

@section('title', 'Servicio No Disponible')
@section('code', '503')
@section('message', translate($exception->getMessage() ?: 'Service Unavailable'))
