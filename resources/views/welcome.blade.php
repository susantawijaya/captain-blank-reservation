@extends('layouts.app')

@section('title', 'Captain Blank Reservation')

@section('content')
    @include('home.sections.hero')
    @include('home.sections.availability-bridge')
    @include('home.sections.service-summary')
    @include('home.sections.destination-preview')
    @include('home.sections.package-preview')
    @include('home.sections.review-preview')
    @include('home.sections.contact-cta')
@endsection
