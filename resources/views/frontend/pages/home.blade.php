@extends('frontend.layout')
@section('pages')

        <x-frontend.hero />
            <!-- Regular Pentanik TV Product Section -->
        <x-frontend.category />
        <section class="product-section">
            <div class="container page-wrapper">

                <!-- Heading & description -->
                <div class="text-center mb-4 mb-md-5">
                    <h2 class="section-title mb-2">Our Premium Caps 2025</h2>
                    <p class="section-subtitle mx-auto text-muted">
                        Dear Customer, the collections you see under this headline are our regular Caps.
                        These are our best selling Caps. You can enjoy a quality Cap within your limited
                        budget. These all are budget friendly collections.
                    </p>
                </div>

                <!-- Products grid -->
                <div class="row g-4">
                    <x-frontend.product-card />
                </div>
            </div>
        </section>
@endsection