@extends('frontend.layout')

@section('pages')
    <div class="content-wrapper">
        <div class="container page-wrapper">

            <form method="GET"
                  action="{{ isset($currentCategory) ? route('productfilter', $currentCategory->slug) : route('allproducts') }}"
                  id="filterForm">

                <!-- Mobile filter button -->
                <div class="d-lg-none mb-3 d-flex justify-content-between">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#filterOffcanvas">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <div class="d-flex gap-2">
                        @php
                            $currentShow = request('show', $perPage ?? 30);
                            $currentSort = request('sort', 'price_asc');
                        @endphp
                        <select class="form-select form-select-sm" name="show"
                                onchange="document.getElementById('filterForm').submit()">
                            <option value="30" {{ $currentShow == 30 ? 'selected' : '' }}>Show: 30</option>
                            <option value="12" {{ $currentShow == 12 ? 'selected' : '' }}>12</option>
                            <option value="24" {{ $currentShow == 24 ? 'selected' : '' }}>24</option>
                            <option value="60" {{ $currentShow == 60 ? 'selected' : '' }}>60</option>
                        </select>

                        <select class="form-select form-select-sm" name="sort"
                                onchange="document.getElementById('filterForm').submit()">
                            <option value="price_asc" {{ $currentSort === 'price_asc' ? 'selected' : '' }}>Price Low to High</option>
                            <option value="price_desc" {{ $currentSort === 'price_desc' ? 'selected' : '' }}>Price High to Low</option>
                            <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest First</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Sidebar (hidden on mobile, visible md+) -->
                    <aside class="col-lg-3 sidebar-col">
                        <!-- Price range -->
                        <div class="filter-card">
                            <div class="filter-title mb-2">Price Range</div>
                            <div class="filter-divider"></div>

                            {{-- Slider is just visual; numbers below are what actually submit --}}
                            <input type="range" class="form-range mb-3" min="10" max="700000"
                                   value="{{ request('max_price', 700000) }}">

                            <div class="d-flex justify-content-between align-items-center range-values mb-3">
                                <input type="number" class="form-control form-control-sm me-2" name="min_price"
                                       placeholder="Min" value="{{ request('min_price', 10) }}">
                                <span class="text-muted small">to</span>
                                <input type="number" class="form-control form-control-sm ms-2" name="max_price"
                                       placeholder="Max" value="{{ request('max_price', 700000) }}">
                            </div>

                            <button class="filter-btn w-100" type="submit">Filter</button>
                        </div>

                        <!-- Availability -->
                        @php
                            $currentAvailability = request('availability');
                        @endphp
                        <div class="filter-card">
                            <div class="d-flex justify-content-between align-items-center filter-toggle"
                                 data-bs-toggle="collapse" data-bs-target="#availabilityFilter" aria-expanded="true">
                                <span class="filter-title">Availability</span>
                                <i class="bi bi-chevron-down small"></i>
                            </div>
                            <div id="availabilityFilter" class="collapse show mt-2">
                                <div class="filter-divider"></div>

                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="availability" id="availAll"
                                           value=""
                                           {{ $currentAvailability === null || $currentAvailability === '' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="availAll">All</label>
                                </div>

                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="availability" id="inStock"
                                           value="in" {{ $currentAvailability === 'in' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inStock">In Stock</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="availability" id="outStock"
                                           value="out" {{ $currentAvailability === 'out' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="outStock">Out of Stock</label>
                                </div>
                            </div>
                        </div>

                        <!-- Category + Sub Category tree -->
                        <div class="filter-card">
                            <div class="d-flex justify-content-between align-items-center filter-toggle"
                                 data-bs-toggle="collapse" data-bs-target="#categoryFilter" aria-expanded="true">
                                <span class="filter-title">Category</span>
                                <i class="bi bi-chevron-down small"></i>
                            </div>
                            <div id="categoryFilter" class="collapse show mt-2">
                                <div class="filter-divider"></div>

                                @php
                                    // Selected category ids
                                    $selectedCategories = (array) (request('category')
                                        ?? (isset($currentCategory) ? [$currentCategory->id] : []));

                                    // Selected sub-category ids
                                    $selectedSubCategories = (array) request('sub_category', []);
                                @endphp

                                {{-- Tree view: all categories passed into view (you can limit to parent_id == null in controller) --}}
                                @foreach ($categories as $category)
                                    <div class="mb-1">
                                        {{-- Main category checkbox --}}
                                        <div class="form-check mb-1">
                                            <input class="form-check-input category-checkbox"
                                                   type="checkbox"
                                                   name="category[]"
                                                   id="cat{{ $category->id }}"
                                                   value="{{ $category->id }}"
                                                   data-url="{{ route('productfilter', $category->slug) }}"
                                                   {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}
                                                   onchange="handleCategoryChange()">
                                            <label class="form-check-label fw-semibold" for="cat{{ $category->id }}">
                                                {{ $category->name }}
                                            </label>
                                        </div>

                                        {{-- Nested subcategories (children) --}}
                                        @if ($category->children && $category->children->count())
                                            <div class="ms-3 ps-2 border-start small">
                                                @foreach ($category->children as $sub)
                                                    <div class="form-check mb-1">
                                                        <input class="form-check-input"
                                                               type="checkbox"
                                                               name="sub_category[]"
                                                               id="sub{{ $sub->id }}"
                                                               value="{{ $sub->id }}"
                                                               {{ in_array($sub->id, $selectedSubCategories) ? 'checked' : '' }}
                                                               onchange="document.getElementById('filterForm').submit()">
                                                        <label class="form-check-label" for="sub{{ $sub->id }}">
                                                            {{ $sub->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                            </div>
                        </div>

                        {{-- Color, Brand, Size filters are intentionally removed as per instructions --}}
                    </aside>

                    <!-- Product listing -->
                    <section class="col-lg-9">
                        <!-- Header / sort bar -->
                        <div class="listing-header-card mb-4 d-none d-lg-flex align-items-center justify-content-between">
                            <div>
                                <div class="listing-header-title">
                                    @if (isset($currentCategory))
                                        {{ $currentCategory->name }} Price in Bangladesh (2025 Update)
                                    @else
                                        Fashionable Caps price in Bangladesh (2025 Update)
                                    @endif
                                </div>
                                <small class="text-muted">
                                    @if (isset($currentCategory))
                                        Updated price list for {{ $currentCategory->name }} from The Brain.
                                    @else
                                        Updated price list for Caps.
                                    @endif
                                </small>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Show:</span>
                                    <select class="form-select form-select-sm" style="width:80px;" name="show"
                                            onchange="document.getElementById('filterForm').submit()">
                                        <option value="30" {{ $currentShow == 30 ? 'selected' : '' }}>30</option>
                                        <option value="12" {{ $currentShow == 12 ? 'selected' : '' }}>12</option>
                                        <option value="24" {{ $currentShow == 24 ? 'selected' : '' }}>24</option>
                                        <option value="60" {{ $currentShow == 60 ? 'selected' : '' }}>60</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Sort by:</span>
                                    <select class="form-select form-select-sm" style="width:160px;" name="sort"
                                            onchange="document.getElementById('filterForm').submit()">
                                        <option value="price_asc" {{ $currentSort === 'price_asc' ? 'selected' : '' }}>
                                            Price Low to High
                                        </option>
                                        <option value="price_desc" {{ $currentSort === 'price_desc' ? 'selected' : '' }}>
                                            Price High to Low
                                        </option>
                                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>
                                            Newest First
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Products grid -->
                        <div class="row g-4">
                            @forelse($products as $product)
                                @php
                                    $basePrice    = $product->price;
                                    $offerPrice   = $product->offer_price ?? null;
                                    $currentPrice = $offerPrice && $offerPrice < $basePrice ? $offerPrice : $basePrice;
                                    $oldPrice     = $offerPrice && $offerPrice < $basePrice ? $basePrice : null;
                                    $discount     = $oldPrice
                                                    ? round((($oldPrice - $currentPrice) / $oldPrice) * 100)
                                                    : null;
                                @endphp

                                <div class="col-6 col-md-4">
                                    <div class="product-card h-100 d-flex flex-column text-center">
                                        @if ($discount)
                                            <div class="product-badge">Save: {{ $discount }}%</div>
                                        @endif

                                        <div class="product-image-wrapper">
                                            <a href="{{ route('singleproduct', $product->id) }}">
                                                <img src="{{ asset('storage') . '/' . ($product->thumbnail ?? $product->image ?? '') }}"
                                                     onerror="this.src='https://via.placeholder.com/330x190?text=Product';"
                                                     alt="{{ $product->name }}"
                                                     style="object-fit: contain">
                                            </a>
                                        </div>

                                        <div class="mt-2 flex-grow-1">
                                            <h3 class="product-title">
                                                <a style="text-decoration: none; color:black;"
                                                   href="{{ route('singleproduct', $product->id) }}">
                                                    {{ $product->name }}
                                                </a>
                                            </h3>
                                        </div>

                                        <div class="product-divider"></div>

                                        <div class="product-price mb-3">
                                            <span class="price-current">{{ currency($currentPrice) }}</span>
                                            @if ($oldPrice)
                                                <span class="price-old">{{ currency($oldPrice) }}</span>
                                            @endif
                                        </div>

                                        <button class="btn btn-product w-100" type="button">
                                            Buy Now
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">No products found for this filter.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </section>
                </div>
            </form>
        </div>
    </div>

    {{-- Script to change form action based on selected category --}}
    <script>
        function handleCategoryChange() {
            const form = document.getElementById('filterForm');
            const allCategoryCheckboxes = document.querySelectorAll('.category-checkbox');
            const checked = Array.from(allCategoryCheckboxes).filter(cb => cb.checked);

            if (checked.length === 0) {
                // No category selected → go to all products
                form.action = "{{ route('allproducts') }}";
            } else {
                // Use first checked category's productfilter URL
                const first = checked[0];
                const url = first.dataset.url;
                form.action = url;
            }

            form.submit();
        }
    </script>
@endsection
