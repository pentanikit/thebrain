@extends('frontend.layout')

@section('style')
    <style>
        :root{
            --primary:#2563eb;
            --primary-soft:#eff4ff;
            --dark:#111827;
        }

        *{box-sizing:border-box;}

        body{
            font-family:'Inter',system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            background: radial-gradient(circle at top left,#e0f2fe,#f9fafb 40%);
            color:var(--dark);
        }

        .page-wrapper{
            max-width:1200px;
        }

        /* Navbar */
        .navbar{
            backdrop-filter:blur(16px);
            background:rgba(255,255,255,0.9) !important;
            border-bottom:1px solid rgba(148,163,184,.25);
        }
        .navbar-brand img{height:34px;}
        .nav-link{font-weight:500;color:#4b5563 !important;}
        .nav-link.active{color:var(--primary) !important;}

        .search-wrapper .form-control{
            border-left:0;
            box-shadow:none;
        }
        .search-wrapper .input-group-text{
            border-right:0;
            background:transparent;
        }

        .content-wrapper{
            margin-top:88px;
            padding-top:1.5rem;
            padding-bottom:3rem;
        }
        @media (max-width:575.98px){
            .content-wrapper{
                margin-top:76px;
            }
        }

        /* Cart dropdown */
        .cart-dropdown-menu{
            width:min(340px,100vw - 1.5rem);
            border-radius:16px;
            border:1px solid #e5e7eb;
        }
        .cart-items-list{max-height:260px;overflow-y:auto;}
        .cart-item-thumb{
            width:60px;height:60px;object-fit:contain;border-radius:8px;background:#f9fafb;
        }

        /* Filter sidebar */
        .filter-card{
            border-radius:16px;
            border:1px solid #e5e7eb;
            background:#ffffff;
            padding:1rem 1.1rem 1.1rem;
            margin-bottom:1rem;
        }
        .filter-title{
            font-weight:600;
            font-size:.95rem;
        }
        .filter-divider{
            height:1px;
            background:#e5e7eb;
            margin:.7rem 0 .6rem;
        }
        .range-values input{
            width:100%;
            max-width:100px;
            font-size:.85rem;
        }
        .filter-btn{
            background:#16a34a;
            border:none;
            color:#fff;
            font-size:.9rem;
            padding:.45rem 1.2rem;
            border-radius:.45rem;
        }
        .filter-btn:hover{
            background:#15803d;
        }

        /* Range slider */
        .form-range::-webkit-slider-thumb{
            background:var(--primary);
        }
        .form-range::-moz-range-thumb{
            background:var(--primary);
        }
        .form-check-label{
            font-size:.9rem;
        }

        /* Accordion caret */
        .filter-toggle{
            cursor:pointer;
        }

        /* Product cards */
        .product-card{
            position:relative;
            border-radius:22px;
            border:1px solid #e5e7eb;
            background:#ffffff;
            padding:1.2rem 1.3rem 1.4rem;
            transition:all .18s ease;
            box-shadow:0 10px 30px rgba(15,23,42,.04);
        }
        .product-card:hover{
            transform:translateY(-4px);
            box-shadow:0 18px 38px rgba(15,23,42,.10);
            border-color:var(--primary-soft);
        }
        .product-badge{
            position:absolute;
            top:14px;
            left:14px;
            background:#111827;
            color:#f9fafb;
            padding:.25rem .7rem;
            font-size:.75rem;
            border-radius:999px;
            font-weight:500;
        }
        .product-image-wrapper{
            text-align:center;
            padding:1.5rem 0 .25rem;
            min-height:170px;
        }
        .product-image-wrapper img{
            max-height:160px;
            width:auto;
            max-width:100%;
            object-fit:contain;
        }
        .product-title{
            font-size:.95rem;
            font-weight:600;
            color:#1f2937;
            min-height:2.7em;
        }
        .product-divider{
            height:1px;
            background:#e5e7eb;
            margin:.9rem 0 1rem;
        }
        .product-price{font-size:.98rem;}
        .price-current{
            color:#dc2626;
            font-weight:700;
            margin-right:.35rem;
        }
        .price-old{
            color:#9ca3af;
            text-decoration:line-through;
            font-size:.9rem;
        }
        .btn-product{
            background:var(--primary-soft);
            border:none;
            font-weight:600;
            color:#1d4ed8;
            border-radius:10px;
            padding:.55rem 1rem;
        }
        .btn-product:hover{
            background:var(--primary);
            color:#ffffff;
        }

        /* Listing header bar */
        .listing-header-title{
            font-size:1.1rem;
            font-weight:600;
        }
        .listing-header-card{
            border-radius:16px;
            background:#ffffff;
            border:1px solid #e5e7eb;
            padding:1rem 1.2rem;
        }

        /* Mobile filter button */
        @media (max-width:991.98px){
            .sidebar-col{
                display:none;
            }
        }
    </style>
@endsection

@section('pages')
<div class="content-wrapper">
    <div class="container page-wrapper">

        {{-- MOBILE: Filter + Show/Sort --}}
        <div class="d-lg-none mb-3 d-flex justify-content-between align-items-center">
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>

            <div class="d-flex gap-2">
                {{-- Show per page --}}
                <form method="GET" action="{{ url()->current() }}" class="d-flex gap-2">
                    {{-- keep other filters --}}
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                    <input type="hidden" name="availability" value="{{ request('availability') }}">
                    {{-- <input type="hidden" name="brand" value="{{ request('brand') }}">
                    <input type="hidden" name="size" value="{{ request('size') }}">
                    <input type="hidden" name="color" value="{{ request('color') }}"> --}}
                    <input type="hidden" name="sort" value="{{ request('sort', 'price_asc') }}">

                    <select class="form-select form-select-sm"
                            name="show"
                            onchange="this.form.submit()">
                        <option value="30" {{ request('show', 30) == 30 ? 'selected' : '' }}>Show: 30</option>
                        <option value="12" {{ request('show') == 12 ? 'selected' : '' }}>12</option>
                        <option value="24" {{ request('show') == 24 ? 'selected' : '' }}>24</option>
                        <option value="60" {{ request('show') == 60 ? 'selected' : '' }}>60</option>
                    </select>

                    {{-- Sort --}}
                    <select class="form-select form-select-sm"
                            name="sort"
                            onchange="this.form.submit()">
                        <option value="price_asc"  {{ request('sort', 'price_asc') == 'price_asc' ? 'selected' : '' }}>Price Low to High</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price High to Low</option>
                        <option value="newest"     {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="row g-4">
            {{-- SIDEBAR (Desktop) --}}
            <aside class="col-lg-3 sidebar-col">
                <form method="GET" action="{{ url()->current() }}">
                    {{-- keep show + sort + search --}}
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="show" value="{{ request('show', 30) }}">
                    <input type="hidden" name="sort" value="{{ request('sort', 'price_asc') }}">

                    {{-- Price range --}}
                    <div class="filter-card">
                        <div class="filter-title mb-2">Price Range</div>
                        <div class="filter-divider"></div>

                        @php
                            $minPrice = request('min_price', 10);
                            $maxPrice = request('max_price', 700000);
                        @endphp

                        <input type="range"
                               class="form-range mb-3"
                               min="10"
                               max="700000"
                               value="{{ $maxPrice }}"
                               oninput="document.getElementById('maxPriceInputDesktop').value = this.value">

                        <div class="d-flex justify-content-between align-items-center range-values mb-3">
                            <input type="number"
                                   class="form-control form-control-sm me-2"
                                   name="min_price"
                                   value="{{ $minPrice }}">
                            <span class="text-muted small">to</span>
                            <input type="number"
                                   class="form-control form-control-sm ms-2"
                                   id="maxPriceInputDesktop"
                                   name="max_price"
                                   value="{{ $maxPrice }}">
                        </div>

                        <button class="filter-btn w-100" type="submit">Filter</button>
                    </div>

                    {{-- Availability --}}
                    <div class="filter-card">
                        <div class="d-flex justify-content-between align-items-center filter-toggle"
                             data-bs-toggle="collapse" data-bs-target="#availabilityFilter" aria-expanded="true">
                            <span class="filter-title">Availability</span>
                            <i class="bi bi-chevron-down small"></i>
                        </div>
                        <div id="availabilityFilter" class="collapse show mt-2">
                            <div class="filter-divider"></div>
                            <div class="form-check mb-1">
                                <input class="form-check-input"
                                       type="radio"
                                       name="availability"
                                       id="inStock"
                                       value="in"
                                       {{ request('availability') === 'in' ? 'checked' : '' }}>
                                <label class="form-check-label" for="inStock">In Stock</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="availability"
                                       id="outStock"
                                       value="out"
                                       {{ request('availability') === 'out' ? 'checked' : '' }}>
                                <label class="form-check-label" for="outStock">Out Stock</label>
                            </div>
                        </div>
                    </div>

                    {{-- Brand --}}
                    {{-- <div class="filter-card">
                        <div class="d-flex justify-content-between align-items-center filter-toggle"
                             data-bs-toggle="collapse" data-bs-target="#brandFilter" aria-expanded="true">
                            <span class="filter-title">Brand</span>
                            <i class="bi bi-chevron-down small"></i>
                        </div>
                        <div id="brandFilter" class="collapse show mt-2">
                            <div class="filter-divider"></div>

                            @php
                                $currentBrand = request('brand');
                            @endphp

                            @forelse($brands ?? [] as $brand)
                                @if($brand)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input"
                                               type="radio"
                                               name="brand"
                                               id="brand_{{ \Illuminate\Support\Str::slug($brand) }}"
                                               value="{{ $brand }}"
                                               {{ $currentBrand === $brand ? 'checked' : '' }}>
                                        <label class="form-check-label" for="brand_{{ \Illuminate\Support\Str::slug($brand) }}">
                                            {{ $brand }}
                                        </label>
                                    </div>
                                @endif
                            @empty
                                <small class="text-muted">No brand filter.</small>
                            @endforelse
                        </div>
                    </div> --}}

                    {{-- Size --}}
                    {{-- <div class="filter-card">
                        <div class="d-flex justify-content-between align-items-center filter-toggle"
                             data-bs-toggle="collapse" data-bs-target="#sizeFilter" aria-expanded="true">
                            <span class="filter-title">Size</span>
                            <i class="bi bi-chevron-down small"></i>
                        </div>
                        <div id="sizeFilter" class="collapse show mt-2">
                            <div class="filter-divider"></div>

                            @php
                                $currentSize = request('size');
                            @endphp

                            @forelse($sizes ?? [] as $size)
                                @if($size)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input"
                                               type="radio"
                                               name="size"
                                               id="size_{{ $size }}"
                                               value="{{ $size }}"
                                               {{ (string)$currentSize === (string)$size ? 'checked' : '' }}>
                                        <label class="form-check-label" for="size_{{ $size }}">{{ $size }}</label>
                                    </div>
                                @endif
                            @empty
                                <small class="text-muted">No size filter.</small>
                            @endforelse
                        </div>
                    </div> --}}

                    {{-- Color --}}
                    {{-- <div class="filter-card">
                        <div class="d-flex justify-content-between align-items-center filter-toggle"
                             data-bs-toggle="collapse" data-bs-target="#colorFilter" aria-expanded="true">
                            <span class="filter-title">Color</span>
                            <i class="bi bi-chevron-down small"></i>
                        </div>
                        <div id="colorFilter" class="collapse show mt-2">
                            <div class="filter-divider"></div>

                            @php
                                $currentColor = request('color');
                            @endphp

                            @forelse($colors ?? [] as $color)
                                @if($color)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input"
                                               type="radio"
                                               name="color"
                                               id="color_{{ \Illuminate\Support\Str::slug($color) }}"
                                               value="{{ $color }}"
                                               {{ $currentColor === $color ? 'checked' : '' }}>
                                        <label class="form-check-label" for="color_{{ \Illuminate\Support\Str::slug($color) }}">
                                            {{ $color }}
                                        </label>
                                    </div>
                                @endif
                            @empty
                                <small class="text-muted">No color filter.</small>
                            @endforelse
                        </div>
                    </div> --}}

                    {{-- Apply button already in price card; you can add another here if চাই --}}
                </form>
            </aside>

            {{-- MAIN PRODUCT LISTING --}}
            <section class="col-lg-9">
                {{-- Desktop header / sort bar --}}
                <div class="listing-header-card mb-4 d-none d-lg-flex align-items-center justify-content-between">
                    <div>
                        <div class="listing-header-title">
                            {{ $pageTitle ?? 'Fashionable premium caps (2025 Update)' }}
                        </div>
                        <small class="text-muted">
                            @if($currentCategory)
                                Showing products in "{{ $currentCategory->name }}"
                            @elseif(request('q'))
                                Search result for: "{{ request('q') }}"
                            @else
                                Updated price list for Pentanik LED & Smart TVs.
                            @endif
                        </small>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        {{-- Show per page --}}
                        <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
                            {{-- keep filters --}}
                            <input type="hidden" name="q" value="{{ request('q') }}">
                            <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                            <input type="hidden" name="availability" value="{{ request('availability') }}">
                            <input type="hidden" name="brand" value="{{ request('brand') }}">
                            <input type="hidden" name="size" value="{{ request('size') }}">
                            <input type="hidden" name="color" value="{{ request('color') }}">
                            <input type="hidden" name="sort" value="{{ request('sort', 'price_asc') }}">

                            <span class="small text-muted">Show:</span>
                            <select class="form-select form-select-sm" name="show" style="width:80px;"
                                    onchange="this.form.submit()">
                                <option value="30" {{ request('show', 30) == 30 ? 'selected' : '' }}>30</option>
                                <option value="12" {{ request('show') == 12 ? 'selected' : '' }}>12</option>
                                <option value="24" {{ request('show') == 24 ? 'selected' : '' }}>24</option>
                                <option value="60" {{ request('show') == 60 ? 'selected' : '' }}>60</option>
                            </select>
                        </form>

                        {{-- Sort --}}
                        <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
                            {{-- keep filters --}}
                            <input type="hidden" name="q" value="{{ request('q') }}">
                            <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                            <input type="hidden" name="availability" value="{{ request('availability') }}">
                            <input type="hidden" name="brand" value="{{ request('brand') }}">
                            <input type="hidden" name="size" value="{{ request('size') }}">
                            <input type="hidden" name="color" value="{{ request('color') }}">
                            <input type="hidden" name="show" value="{{ request('show', 30) }}">

                            <span class="small text-muted">Sort by:</span>
                            <select class="form-select form-select-sm" name="sort" style="width:160px;"
                                    onchange="this.form.submit()">
                                <option value="price_asc"  {{ request('sort', 'price_asc') == 'price_asc' ? 'selected' : '' }}>Price Low to High</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price High to Low</option>
                                <option value="newest"     {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Products grid --}}
                <div class="row g-4">
                    @forelse($products as $product)
                        @php
                            $oldPrice   = $product->old_price;
                            $current    = $product->offer_price ?? $product->price;
                            $discount   = ($oldPrice && $oldPrice > $current)
                                ? round((($oldPrice - $current) / $oldPrice) * 100)
                                : null;
                        @endphp

                        <div class="col-6 col-md-4">
                            <div class="product-card h-100 d-flex flex-column">
                                @if($discount)
                                    <div class="product-badge">Save: {{ $discount }}%</div>
                                @endif

                              
                                    <img src="{{ asset('storage').'/'.$product->thumbnail }}"
                                         alt="{{ $product->name }}" style="object-fit: cover; border-radius: 5%;">
                               

                                <div class="mt-2 flex-grow-1">
                                    <h3 class="product-title">
                                        {{ $product->name }}
                                    </h3>
                                </div>

                                <div class="product-divider"></div>

                                <div class="product-price mb-3">
                                    <span class="price-current">
                                        @if(function_exists('currency_bdt'))
                                            {{ currency_bdt($current) }}
                                        @else
                                            ৳{{ number_format($current) }}
                                        @endif
                                    </span>

                                    @if($oldPrice)
                                        <span class="price-old">
                                            @if(function_exists('currency_bdt'))
                                                {{ currency_bdt($oldPrice) }}
                                            @else
                                                ৳{{ number_format($oldPrice) }}
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                <a href=""
                                   class="btn btn-product w-100">
                                    Buy Now
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted mb-0">No products found.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </section>
        </div>
    </div>
</div>

{{-- MOBILE FILTER OFFCANVAS --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title" id="filterOffcanvasLabel">Filter Products</h6>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="{{ url()->current() }}">
            {{-- keep show + sort + search --}}
            <input type="hidden" name="q" value="{{ request('q') }}">
            <input type="hidden" name="show" value="{{ request('show', 30) }}">
            <input type="hidden" name="sort" value="{{ request('sort', 'price_asc') }}">

            {{-- Price range --}}
            <div class="filter-card">
                <div class="filter-title mb-2">Price Range</div>
                <div class="filter-divider"></div>

                @php
                    $minPriceMobile = request('min_price', 10);
                    $maxPriceMobile = request('max_price', 700000);
                @endphp

                <input type="range"
                       class="form-range mb-3"
                       min="10"
                       max="700000"
                       value="{{ $maxPriceMobile }}"
                       oninput="document.getElementById('maxPriceInputMobile').value = this.value">

                <div class="d-flex justify-content-between align-items-center range-values mb-3">
                    <input type="number"
                           class="form-control form-control-sm me-2"
                           name="min_price"
                           value="{{ $minPriceMobile }}">
                    <span class="text-muted small">to</span>
                    <input type="number"
                           class="form-control form-control-sm ms-2"
                           id="maxPriceInputMobile"
                           name="max_price"
                           value="{{ $maxPriceMobile }}">
                </div>
            </div>

            {{-- Availability --}}
            <div class="filter-card">
                <div class="d-flex justify-content-between align-items-center filter-toggle"
                     data-bs-toggle="collapse" data-bs-target="#availabilityFilterMobile" aria-expanded="true">
                    <span class="filter-title">Availability</span>
                    <i class="bi bi-chevron-down small"></i>
                </div>
                <div id="availabilityFilterMobile" class="collapse show mt-2">
                    <div class="filter-divider"></div>
                    <div class="form-check mb-1">
                        <input class="form-check-input"
                               type="radio"
                               name="availability"
                               id="inStockMobile"
                               value="in"
                               {{ request('availability') === 'in' ? 'checked' : '' }}>
                        <label class="form-check-label" for="inStockMobile">In Stock</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input"
                               type="radio"
                               name="availability"
                               id="outStockMobile"
                               value="out"
                               {{ request('availability') === 'out' ? 'checked' : '' }}>
                        <label class="form-check-label" for="outStockMobile">Out Stock</label>
                    </div>
                </div>
            </div>

            {{-- Brand --}}
            <div class="filter-card">
                <div class="d-flex justify-content-between align-items-center filter-toggle"
                     data-bs-toggle="collapse" data-bs-target="#brandFilterMobile" aria-expanded="true">
                    <span class="filter-title">Brand</span>
                    <i class="bi bi-chevron-down small"></i>
                </div>
                <div id="brandFilterMobile" class="collapse show mt-2">
                    <div class="filter-divider"></div>

                    @php
                        $currentBrandMobile = request('brand');
                    @endphp

                    @forelse($brands ?? [] as $brand)
                        @if($brand)
                            <div class="form-check mb-1">
                                <input class="form-check-input"
                                       type="radio"
                                       name="brand"
                                       id="brand_mobile_{{ \Illuminate\Support\Str::slug($brand) }}"
                                       value="{{ $brand }}"
                                       {{ $currentBrandMobile === $brand ? 'checked' : '' }}>
                                <label class="form-check-label" for="brand_mobile_{{ \Illuminate\Support\Str::slug($brand) }}">
                                    {{ $brand }}
                                </label>
                            </div>
                        @endif
                    @empty
                        <small class="text-muted">No brand filter.</small>
                    @endforelse
                </div>
            </div>

            {{-- Size --}}
            <div class="filter-card">
                <div class="d-flex justify-content-between align-items-center filter-toggle"
                     data-bs-toggle="collapse" data-bs-target="#sizeFilterMobile" aria-expanded="true">
                    <span class="filter-title">Size</span>
                    <i class="bi bi-chevron-down small"></i>
                </div>
                <div id="sizeFilterMobile" class="collapse show mt-2">
                    <div class="filter-divider"></div>

                    @php
                        $currentSizeMobile = request('size');
                    @endphp

                    @forelse($sizes ?? [] as $size)
                        @if($size)
                            <div class="form-check mb-1">
                                <input class="form-check-input"
                                       type="radio"
                                       name="size"
                                       id="size_mobile_{{ $size }}"
                                       value="{{ $size }}"
                                       {{ (string)$currentSizeMobile === (string)$size ? 'checked' : '' }}>
                                <label class="form-check-label" for="size_mobile_{{ $size }}">{{ $size }}</label>
                            </div>
                        @endif
                    @empty
                        <small class="text-muted">No size filter.</small>
                    @endforelse
                </div>
            </div>

            {{-- Color --}}
            <div class="filter-card">
                <div class="d-flex justify-content-between align-items-center filter-toggle"
                     data-bs-toggle="collapse" data-bs-target="#colorFilterMobile" aria-expanded="true">
                    <span class="filter-title">Color</span>
                    <i class="bi bi-chevron-down small"></i>
                </div>
                <div id="colorFilterMobile" class="collapse show mt-2">
                    <div class="filter-divider"></div>

                    @php
                        $currentColorMobile = request('color');
                    @endphp

                    @forelse($colors ?? [] as $color)
                        @if($color)
                            <div class="form-check mb-1">
                                <input class="form-check-input"
                                       type="radio"
                                       name="color"
                                       id="color_mobile_{{ \Illuminate\Support\Str::slug($color) }}"
                                       value="{{ $color }}"
                                       {{ $currentColorMobile === $color ? 'checked' : '' }}>
                                <label class="form-check-label" for="color_mobile_{{ \Illuminate\Support\Str::slug($color) }}">
                                    {{ $color }}
                                </label>
                            </div>
                        @endif
                    @empty
                        <small class="text-muted">No color filter.</small>
                    @endforelse
                </div>
            </div>

            <button class="filter-btn w-100 mt-2" type="submit">
                Apply Filters
            </button>
        </form>
    </div>
</div>
@endsection
