<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container page-wrapper">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('homeRoute') }}">
            <!-- Logo -->
            <img src="{{ asset('storage/' . site_setting('logo')) }}"  alt="{{ site_setting('site_title') }}">

            {{-- <h4>The Brain</h4> --}}
        </a>

        <!-- Toggler now opens OFFCANVAS -->
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainNavbarOffcanvas"
            aria-controls="mainNavbarOffcanvas" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav ms-3 me-auto mb-2 mb-lg-0">


                {{-- PRODUCTS NESTED DROPDOWN --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Products
                    </a>

                    @php
                        // Root (parent) categories
                        $rootCategories = categories(); // same as categories(null)
                    @endphp

                    <ul class="dropdown-menu" aria-labelledby="productsDropdown" data-bs-auto-close="outside">
                        @foreach ($rootCategories as $category)

                            @php
                                $children = categories($category->id); // sub categories
                            @endphp

                            @if ($children->isNotEmpty())
                                {{-- Category WITH subcategories --}}
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle"
                                    href="{{ route('productfilter', $category->slug) }}">
                                        {{ $category->name }}
                                    </a>

                                    <ul class="dropdown-menu">
                                        @foreach ($children as $child)
                                            <li>
                                                <a class="dropdown-item"
                                                href="{{ route('productfilter', $category->slug) }}?sub_category[]={{ $child->id }}">
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @else
                                {{-- Category WITHOUT subcategories --}}
                                <li>
                                    <a class="dropdown-item"
                                    href="{{ route('productfilter', $category->slug) }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endif

                        @endforeach
                    </ul>

                </li>


                {{-- OTHER NAV LINKS --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('productfilter', 'caps') }}">Caps</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('productfilter', 'women') }}">Women</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('productfilter', 'men') }}">Men</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('productfilter', 'kids') }}">Kids</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">About</a>
                </li>

            </ul>



            <!-- Search (still hidden on <md, same as before) -->
            <form class="d-none d-md-block me-3 search-wrapper" style="min-width:230px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input class="form-control" type="search" placeholder="Search products…">
                </div>
            </form>

            <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                <!-- user -->
                {{-- <a href="#" class="text-dark fs-5"><i class="bi bi-person-circle"></i></a> --}}

                <!-- cart dropdown -->
                <div class="dropdown">
                    <button class="btn p-0 border-0 bg-transparent text-dark position-relative fs-5" id="cartDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-cart3"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $cart->totalQuantity() }}
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end cart-dropdown-menu p-0 shadow-lg"
                        aria-labelledby="cartDropdown">
                        <!-- header -->
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0 fw-semibold">
                                Cart ({{ $cart->totalQuantity() }} items)
                            </h6>
                        </div>

                        <!-- items -->
                        <div class="cart-items-list">
                            @forelse($items as $item)
                                <div class="d-flex align-items-center p-3 {{ !$loop->first ? 'border-top' : '' }}">
                                    <img src="{{ asset('storage') . '/' . $item->product->thumbnail ?? 'https://via.placeholder.com/80x60?text=TV' }}"
                                        class="cart-item-thumb me-3" alt="{{ $item->product->name }}">
                                    <div class="flex-grow-1">
                                        <div class="small fw-semibold">
                                            {{ $item->product->name }}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="small text-muted">Qty: {{ $item->quantity }}</span>
                                            <span class="small fw-semibold text-danger">
                                                {{ currency($item->total_price) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-muted small">
                                    Your cart is empty.
                                </div>
                            @endforelse
                        </div>

                        <!-- footer -->
                        <div class="p-3 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold">Subtotal</span>
                                <span class="fw-bold text-danger">৳{{ number_format($cart->total) }}</span>
                            </div>
                            <a href="{{ route('cart.showcart') }}" class="btn btn-primary w-100 btn-sm">
                                Go to Checkout
                            </a>
                        </div>
                    </div>


                </div>
                <!-- /cart dropdown -->
            </div>
        </div>

    </div>
</nav>

<!-- Offcanvas for mobile, inline from lg and up -->
<div class="offcanvas offcanvas-start offcanvas-lg" tabindex="-1" id="mainNavbarOffcanvas"
    aria-labelledby="mainNavbarOffcanvasLabel">

    <!-- Mobile-only header inside offcanvas -->
    <div class="offcanvas-header d-lg-none">
        <h5 class="offcanvas-title" id="mainNavbarOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <ul class="navbar-nav ms-3 me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link active" href="#">Products</a></li>
            <li class="nav-item"><a class="nav-link" href="#">TV</a></li>
            <li class="nav-item"><a class="nav-link" href="#">AC</a></li>
            <li class="nav-item"><a class="nav-link" href="#">TV Remote</a></li>
            <li class="nav-item"><a class="nav-link" href="#">About</a></li>
        </ul>

        <!-- Search (still hidden on <md, same as before) -->
        <form class="d-none d-md-block me-3 search-wrapper" style="min-width:230px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input class="form-control" type="search" placeholder="Search products…">
            </div>
        </form>

        <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
            <!-- user -->
            <a href="#" class="text-dark fs-5"><i class="bi bi-person-circle"></i></a>

            <!-- cart dropdown -->
            <div class="dropdown">
                <button class="btn p-0 border-0 bg-transparent text-dark position-relative fs-5" id="cartDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-cart3"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                    </span>
                </button>

                <div class="dropdown-menu dropdown-menu-end cart-dropdown-menu p-0 shadow-lg"
                    aria-labelledby="cartDropdown">
                    <!-- header -->
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-semibold">Cart (3 items)</h6>
                    </div>

                    <!-- items -->
                    <div class="cart-items-list">
                        <!-- Item 1 -->
                        <div class="d-flex align-items-center p-3">
                            <img src="https://via.placeholder.com/80x60?text=24%22" class="cart-item-thumb me-3"
                                alt="Pentanik 24 Basic">
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">
                                    Pentanik 24&quot; Basic TV
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="small text-muted">Qty: 1</span>
                                    <span class="small fw-semibold text-danger">৳7500</span>
                                </div>
                            </div>
                        </div>

                        <!-- Item 2 -->
                        <div class="d-flex align-items-center p-3 border-top">
                            <img src="https://via.placeholder.com/80x60?text=32%22" class="cart-item-thumb me-3"
                                alt="Pentanik 32 Smart">
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">
                                    Pentanik 32&quot; Smart TV
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="small text-muted">Qty: 1</span>
                                    <span class="small fw-semibold text-danger">৳14000</span>
                                </div>
                            </div>
                        </div>

                        <!-- Item 3 -->
                        <div class="d-flex align-items-center p-3 border-top">
                            <img src="https://via.placeholder.com/80x60?text=43%22" class="cart-item-thumb me-3"
                                alt="Pentanik 43 Google TV">
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">
                                    Pentanik 43&quot; Google TV
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="small text-muted">Qty: 1</span>
                                    <span class="small fw-semibold text-danger">৳29900</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- footer -->
                    <div class="p-3 border-top">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Subtotal</span>
                            <span class="fw-bold text-danger">৳51,400</span>
                        </div>
                        <a href="/checkout" class="btn btn-primary w-100 btn-sm">
                            Go to Checkout
                        </a>
                    </div>
                </div>
            </div>
            <!-- /cart dropdown -->
        </div>
    </div>
</div>

<style>
    /* Make sure the navbar container can position dropdowns nicely */
    .navbar .container,
    .navbar .page-wrapper {
        position: relative;
    }

    /* Main mega dropdown panel */
    .mega-dropdown {
        min-width: 420px;
        max-width: min(900px, 100vw - 2rem);
        margin-top: .25rem;
    }

    /* Wrapper */
    .mega-category-wrapper {
        width: 100%;
    }

    /* Grid of category blocks */
    .mega-category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 1rem 1.5rem;
    }

    /* Each category block */
    .mega-category-col {
        padding: .35rem .25rem;
    }

    /* Root category link styles */
    .mega-root-link {
        display: inline-block;
        font-size: .95rem;
        font-weight: 600;
        color: #111827;
        text-decoration: none;
        margin-bottom: .25rem;
        transition: color .15s ease, transform .15s ease;
    }

    .mega-category-col.has-children .mega-root-link {
        margin-bottom: .35rem;
    }

    .mega-root-link:hover {
        color: #2563eb;
        transform: translateY(-1px);
    }

    /* Subcategory list */
    .mega-sub-list {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .mega-sub-item {
        margin-bottom: .1rem;
    }

    .mega-sub-link {
        display: block;
        font-size: .85rem;
        color: #4b5563;
        text-decoration: none;
        padding: .1rem 0;
        transition: color .15s ease, transform .15s ease;
    }

    .mega-sub-link:hover {
        color: #2563eb;
        transform: translateX(2px);
    }

    /* Category with no children – treat as a single link block */
    .mega-category-col.no-children .mega-root-link {
        font-weight: 500;
    }

    /* Desktop-specific alignment */
    @media (min-width: 992px) {
        .nav-item.dropdown.position-static .dropdown-menu.mega-dropdown {
            left: 0;
            right: auto;
            transform: none;
        }
    }

    /* Mobile: let it behave like a normal stacked dropdown */
    @media (max-width: 991.98px) {
        .mega-dropdown {
            max-width: 100%;
            min-width: 100%;
        }

        .mega-category-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* ---------- NESTED DROPDOWN (SIDE MENU) ---------- */

    .dropdown-submenu {
        position: relative;
    }

    /* Arrow for items that have submenu */
    .dropdown-submenu>.dropdown-item.dropdown-toggle::after {
        content: "›";
        float: right;
        font-size: 0.75rem;
        opacity: 0.7;
    }

    /* Position the submenu to the right side */
    .dropdown-submenu>.dropdown-menu {
        top: 0;
        left: 100%;
        margin-left: .25rem;
        margin-right: .25rem;
    }

    /* Show submenu on hover (desktop) */
    @media (min-width: 992px) {
        .dropdown-submenu:hover>.dropdown-menu {
            display: block;
        }
    }

    /* Ensure top-level Products dropdown behaves normally */
    .navbar .dropdown-menu {
        /* optional: slightly wider menu */
        min-width: 220px;
    }

    /* Make sure nested menus don’t break the container on smaller screens */
    @media (max-width: 991.98px) {
        .dropdown-submenu>.dropdown-menu {
            position: static;
            margin-left: 0;
            box-shadow: none;
        }
    }
</style>
