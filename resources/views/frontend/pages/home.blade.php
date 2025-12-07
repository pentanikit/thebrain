@extends('frontend.layout')
@section('pages')

        <x-frontend.hero />
            <!-- Regular Pentanik TV Product Section -->
        <x-frontend.category />
        <x-frontend.product-section1 type="Premium Caps" />
        <x-frontend.product-section2  type="Regular Caps" />
        <x-frontend.product-section3  type="Branded Caps"/>

        <x-frontend.footer />
        {{-- <x-frontend.product-section4 /> --}}
@endsection