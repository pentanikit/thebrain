@extends('frontend.layout')

@push('styles')
    <style>
        .product-gallery-card {
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .product-gallery {
            padding: 1rem;
        }

        /* Thumbs wrapper */
        .product-thumbs {
            max-height: 500px;
            overflow-y: auto;
            width: 30%;
        }

        /* On mobile – thumbnails horizontally at bottom */
        @media (max-width: 767.98px) {
            .product-thumbs {
                width: 100%;
                max-height: 90px;
                overflow-x: auto;
                overflow-y: hidden;

            }
        }

        /* Single thumb button */
        .product-thumb {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 2px;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            display: inline-block;
        }

        .product-thumb img {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 3;
            /* keeps nice proportion */
            object-fit: cover;
            /* crop only edges, keeps composition */
            border-radius: 0.45rem;
        }

        /* Hover & active state */
        .product-thumb:hover,
        .product-thumb.active {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.2);
        }

        /* Main image container */
        .product-main-img {
            border-radius: 1rem;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 260px;
        }

        /* Main image – fit any size */
        .product-main-img img {
            width: 100%;
            max-height: 460px;
            object-fit: contain;
            /* no crop, full image visible */
        }


        .detail-bullets li {
            padding-bottom: .15rem;
        }

        .detail-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .9rem;
            border-radius: 999px;
            background: #f3f4ff;
            font-size: .9rem;
            font-weight: 600;
            color: #111827;
        }

        .detail-pill .price-current {
            color: #dc2626;
            font-weight: 700;
        }

        .detail-pill .price-old {
            color: #9ca3af;
            text-decoration: line-through;
            font-size: .85rem;
        }

        .detail-pill.status-pill {
            background: #f3f4f6;
        }

        .status-out {
            color: #b91c1c;
        }

        .status-in {
            color: #16a34a;
        }

        .btn-buy-now {
            background: var(--primary);
            border: none;
            font-weight: 600;
        }

        .btn-buy-now:hover {
            background: #1d4ed8;
        }

        .btn-add-cart {
            background: var(--primary-soft);
            border: none;
            font-weight: 600;
            color: #1d4ed8;
        }

        .btn-add-cart:hover {
            background: #dbeafe;
            color: #1d4ed8;
        }

        /* Tabs */
        .product-tabs .nav-link {
            border: none;
            border-radius: 0;
            font-weight: 500;
            color: #4b5563;
        }

        .product-tabs .nav-link.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            background: transparent;
        }

        .tab-card {
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 1.5rem 1.5rem 2rem;
        }

        .specs-table th,
        .specs-table td {
            font-size: .9rem;
            padding: .55rem .75rem;
        }

        .specs-table th {
            width: 30%;
            background: #f9fafb;
            font-weight: 600;
        }

        footer {
            border-top: 1px solid rgba(148, 163, 184, .4);
        }
    </style>
@endpush



@section('pages')
    <div class="content-wrapper">
        <div class="container page-wrapper">
            <!-- Top product info -->
            <div class="row g-4 align-items-start mb-4">
                <!-- Gallery -->
                <div class="col-lg-6">
                    @php
                        $mainImage = $product->thumbnail
                            ? asset('storage/' . $product->thumbnail)
                            : ($product->images->first()
                                ? asset('storage/' . $product->images->first()->path)
                                : 'https://via.placeholder.com/640x360?text=No+Image');
                    @endphp

                    <div class="product-gallery-card">
                        <div class="product-gallery d-flex flex-column flex-md-row gap-3">
                            {{-- Thumbnails --}}
                            <div class="product-thumbs d-flex flex-row flex-md-column gap-2 me-md-3 mb-2 mb-md-0">
                                {{-- Thumbnail image (main)
                                @if ($product->thumbnail)
                                    <button type="button" class="product-thumb active" data-large="{{ $mainImage }}">
                                        <img src="{{ $mainImage }}" alt="{{ $product->name }}">
                                    </button>
                                @endif --}}

                                {{-- Extra gallery images --}}
                                @forelse ($product->images as $image)
                                    @php $imageUrl = asset('storage/'.$image->path); @endphp
                                    <button type="button"
                                        class="product-thumb {{ !$product->thumbnail && $loop->first ? 'active' : '' }}"
                                        data-large="{{ $imageUrl }}">
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}">
                                    </button>
                                @empty
                                    @if (!$product->thumbnail)
                                        <div class="text-muted small px-2">
                                            No media found
                                        </div>
                                    @endif
                                @endforelse
                            </div>

                            {{-- Main image --}}
                            <div class="flex-grow-1">
                                <div class="product-main-img p-3">
                                    <img src="{{ $mainImage }}" alt="{{ $product->name }}">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Details -->
                <div class="col-lg-6">
                    <h1 class="product-title-main mb-2">
                        {{ $product->name }}
                    </h1>

                    <ul class="list-unstyled small text-muted detail-bullets mb-3">
                        {{-- <li>1 Year Parts Panel Replacement Guarantee</li>
                        <li>10 Years Free Service Warranty (Without Parts)</li>
                        <li>EMI system available</li>
                        <li>Home delivery + installation (Dhaka City)</li>
                        <li>Outside Dhaka: Courier delivery</li> --}}
                        {{ $product->description->body ?? '' }}
                    </ul>

                    <p class="mb-3 mx-3 fw-semibold">
                        Details : <a href="tel:+8801880162323" class="text-decoration-none text-primary">
                            +8801880162323</a>
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <div class="detail-pill">
                            <span>Price:</span>
                            <span class="price-current">{{ currency($product->offer_price ?? $product->old_price) }}</span>
                            <span class="price-old">{{ currency($product->price) }}</span>
                        </div>

                        <div class="detail-pill status-pill">
                            <span>Status:</span>
                            @if ($product->stock_status == 'in_stock')
                                <span class="status-in ">{{ $product->stock_status }}</span>
                            @else
                                <span class="status-out ">{{ $product->stock_status }}</span>
                            @endif

                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('cart.addcart', $product->id) }}" class="btn btn-buy-now px-4 py-2 text-white">
                            Buy Now
                        </a>
                        <a class="btn btn-add-cart px-4 py-2" href="{{ route('cart.addcart', $product->id) }}">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                        </a>
                    </div>
                    <div class="small text-muted my-3">
                        {{ $product->descriptions[0]->body ?? '' }}
                    </div>
                    <p class="small text-muted mb-0">
                        * Price in Bangladesh may change without prior notice. Please confirm the latest price over phone.
                    </p>
                </div>
            </div>

            <!-- Tabs: specs / description / etc. -->
            <ul class="nav nav-tabs product-tabs mb-3" id="productTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs"
                        type="button" role="tab">
                        Specifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button"
                        role="tab">
                        Descriptions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="support-tab" data-bs-toggle="tab" data-bs-target="#support" type="button"
                        role="tab">
                        Support
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button"
                        role="tab">
                        Galleries
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="video-tab" data-bs-toggle="tab" data-bs-target="#video" type="button"
                        role="tab">
                        Video
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button"
                        role="tab">
                        Reviews
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Specifications -->
                <div class="tab-pane fade show active" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                    <div class="tab-card">
                        <h5 class="mb-3">{{ $product->name }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered specs-table mb-0">
                                <tbody>
                                    @forelse ($product->specifications as $item)
                                        <tr>
                                            <th>{{ $item->value[0] }}</th>
                                            <td>{{ $item->value[1] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <p>Nothing Found</p>
                                        </tr>
                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="tab-pane fade" id="desc" role="tabpanel" aria-labelledby="desc-tab">
                    <div class="tab-card">
                        <h5 class="mb-3">Product Description</h5>
                        <p class="small text-muted">
                            {{ $product->descriptions[0]->body ?? 'No description available' }}
                        </p>

                    </div>
                </div>

                <!-- Support -->
                <div class="tab-pane fade" id="support" role="tabpanel" aria-labelledby="support-tab">
                    <div class="tab-card">
                        <h5 class="mb-3">Support & Warranty</h5>
                        <p class="small text-muted mb-2">
                            For any service, please call our hotline or message our Facebook page.
                        </p>

                    </div>
                </div>

                <!-- Galleries -->
                <div class="tab-pane fade" id="gallery" role="tabpanel" aria-labelledby="gallery-tab">
                    <div class="tab-card">
                        <h5 class="mb-3">Image Gallery</h5>
                        <div class="row g-3">
                            @forelse ($product->images as $item)
                                <div class="col-6 col-md-3">
                                    <img src="{{ asset('storage').'/'.$item->path }}" class="img-fluid rounded-3"
                                        alt="">
                                </div>
                            @empty
                                <div>
                                    No media available
                                </div>
                            @endforelse


                        </div>
                    </div>
                </div>

                <!-- Video -->
                {{-- <div class="tab-pane fade" id="video" role="tabpanel" aria-labelledby="video-tab">
                    <div class="tab-card">
                        <h5 class="mb-3">Product Video</h5>
                        <div class="ratio ratio-16x9">
                            <!-- Replace src with your YouTube embed link -->
                            <iframe src="#" title="Product video"
                                allowfullscreen></iframe>
                        </div>
                    </div>
                </div> --}}

                <!-- Reviews -->
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <div class="tab-card">
                        <h5 class="mb-3">Customer Reviews</h5>

                        <p class="small text-muted mb-3">There are no reviews yet. Be the first to review this product.</p>
                        <button class="btn btn-outline-primary btn-sm">Write a Review</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.product-gallery-card').forEach(function(card) {
                const mainImg = card.querySelector('.product-main-img img');
                const thumbs = card.querySelectorAll('.product-thumb');

                if (!mainImg || !thumbs.length) return;

                thumbs.forEach(function(thumb) {
                    thumb.addEventListener('click', function() {
                        const large = this.getAttribute('data-large') || this.querySelector(
                            'img').src;
                        if (!large) return;

                        // change main image
                        mainImg.src = large;

                        // active class switch
                        thumbs.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            });
        });
    </script>
@endpush
