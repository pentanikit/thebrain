<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container page-wrapper">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('homeRoute') }}">
            <!-- Logo -->
            <img src="{{ asset('storage/' . site_setting('logo')) }}" alt="{{ site_setting('site_title') }}">

            {{-- <h4>The Brain</h4> --}}
        </a>
        {{-- MOBILE SEARCH (before hamburger) --}}
        <form class="d-flex d-md-none me-2 search-wrapper sef-search"
            action="{{ route('search.index') }}"
            method="GET"
            autocomplete="off"
            style="flex:1; max-width:220px;">
            <div class="input-group input-group-sm position-relative w-100">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>

                <input name="q"
                    class="form-control"
                    type="search"
                    placeholder="Search…"
                    aria-label="Search products">

                <div class="sef-search-dropdown d-none"
                    role="listbox"
                    aria-label="Search suggestions"></div>
            </div>
        </form>

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
                                    <a class="dropdown-item" href="{{ route('productfilter', $category->slug) }}">
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
            <form class="d-none d-md-block me-3 search-wrapper sef-search" style="min-width:230px;"
                action="{{ route('search.index') }}" method="GET" autocomplete="off">
                <div class="input-group input-group-sm position-relative">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>

                    <input id="sefSearchInput" name="q" class="form-control" type="search"
                        placeholder="Search products…" aria-label="Search products">

                    <div id="sefSearchDropdown" class="sef-search-dropdown d-none" role="listbox"
                        aria-label="Search suggestions"></div>
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

        <ul class="navbar-nav ms-lg-3 me-auto mb-2 mb-lg-0">

            {{-- PRODUCTS NESTED DROPDOWN (same as navbar) --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="productsDropdownMobile" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Products
                </a>

                @php
                    $rootCategories = categories();
                @endphp

                <ul class="dropdown-menu" aria-labelledby="productsDropdownMobile" data-bs-auto-close="outside">
                    @foreach ($rootCategories as $category)
                        @php
                            $children = categories($category->id);
                        @endphp

                        @if ($children->isNotEmpty())
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
                            <li>
                                <a class="dropdown-item" href="{{ route('productfilter', $category->slug) }}">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>

            {{-- OTHER NAV LINKS (same as navbar) --}}
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

        <!-- Search (show inside offcanvas on mobile; desktop already has it in navbar) -->
        <form class="d-block d-md-none mt-3 search-wrapper sef-search" action="{{ route('search.index') }}"
            method="GET" autocomplete="off">
            <div class="input-group input-group-sm position-relative">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>

                <input id="sefSearchInputMobile" name="q" class="form-control" type="search"
                    placeholder="Search products…" aria-label="Search products">

                <div id="sefSearchDropdownMobile" class="sef-search-dropdown d-none" role="listbox"
                    aria-label="Search suggestions"></div>
            </div>
        </form>

        <div class="d-flex align-items-center gap-3 mt-3">
            <!-- user -->
            {{-- <a href="#" class="text-dark fs-5"><i class="bi bi-person-circle"></i></a> --}}

            <!-- cart dropdown (mobile id to avoid duplicate with desktop) -->
            <div class="dropdown">
                <button class="btn p-0 border-0 bg-transparent text-dark position-relative fs-5"
                    id="cartDropdownMobile" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-cart3"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $cart->totalQuantity() }}
                    </span>
                </button>

                <div class="dropdown-menu dropdown-menu-end cart-dropdown-menu p-0 shadow-lg"
                    aria-labelledby="cartDropdownMobile">
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
                                    <div class="small fw-semibold">{{ $item->product->name }}</div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="small text-muted">Qty: {{ $item->quantity }}</span>
                                        <span class="small fw-semibold text-danger">
                                            {{ currency($item->total_price) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-muted small">Your cart is empty.</div>
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


    /* Search suggest (Bootstrap 5 friendly) */
    .sef-search {
        position: relative;
    }

    .sef-search-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .12);
        overflow: hidden;
        max-height: 360px;
        overflow-y: auto;


    }

    .sef-search-item {
        display: flex;
        gap: 10px;
        padding: 10px 12px;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    .sef-search-item:last-child {
        border-bottom: 0;
    }

    .sef-search-item:hover,
    .sef-search-item.active {
        background: rgba(13, 110, 253, .08);
    }

    .sef-search-left {
        width: 42px;
        flex: 0 0 42px;
    }

    .sef-search-thumb {
        width: 42px;
        height: 42px;
        object-fit: cover;
        border-radius: 10px;
        background: #f2f2f2;
        border: 1px solid rgba(0, 0, 0, .08);
    }

    .sef-thumb-placeholder {
        display: block;
    }

    .sef-search-body {
        min-width: 0;
    }

    .sef-search-title {
        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sef-search-meta {
        font-size: 12px;
        opacity: .75;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sef-search-desc {
        font-size: 12px;
        opacity: .7;
        margin-top: 4px;
        line-height: 1.25;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<style>
    /* Desktop: flyout submenu */
    @media (min-width: 992px) {
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu>.dropdown-menu {
            top: 0;
            left: 100%;
            margin-left: .1rem;
        }
    }

    /* Mobile/offcanvas: submenu opens beneath */
    @media (max-width: 991.98px) {
        .dropdown-submenu>.dropdown-menu {
            position: static;
            margin-left: 0;
            padding-left: .5rem;
        }
    }
</style>


@push('scripts')
    <script>
        (() => {

            const endpoint = @json(route('search.suggest'));
            let t = null;

            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (m) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [m]));

            document.querySelectorAll('.sef-search').forEach(wrapper => {

                const input = wrapper.querySelector('input[name="q"]');
                const dropdown = wrapper.querySelector('.sef-search-dropdown');

                if (!input || !dropdown) return;

                let activeIndex = -1;
                let currentItems = [];

                const closeDropdown = () => {
                    dropdown.classList.add('d-none');
                    dropdown.innerHTML = '';
                    activeIndex = -1;
                    currentItems = [];
                };

                const openDropdown = () => dropdown.classList.remove('d-none');

                const render = (items) => {
                    currentItems = items || [];
                    activeIndex = -1;

                    if (!currentItems.length) return closeDropdown();

                    let html = '';
                    currentItems.forEach((it, idx) => {
                        const meta = [it.category, it.sub_category].filter(Boolean).join(' • ');
                        const sku = it.sku ? `SKU: ${esc(it.sku)}` : '';
                        const desc = it.desc ? esc(it.desc) : '';

                        html += `
                            <a class="sef-search-item" href="${esc(it.url)}" role="option" data-idx="${idx}">
                            <div class="sef-search-left">
                                ${it.thumb
                                    ? `<img class="sef-search-thumb" width="60" height="60" src="${esc(it.thumb)}" alt="">`
                                    : `<div class="sef-search-thumb sef-thumb-placeholder"></div>`}
                            </div>
                            <div class="sef-search-body">
                                <div class="sef-search-title">${esc(it.name)}</div>
                                <div class="sef-search-meta">${esc(meta)} ${meta && sku ? ' • ' : ''}${sku}</div>
                                ${desc ? `<div class="sef-search-desc">${desc}</div>` : ''}
                            </div>
                            </a>`;
                    });

                    dropdown.innerHTML = html;
                    openDropdown();
                };

                const setActive = (idx) => {
                    const items = dropdown.querySelectorAll('.sef-search-item');
                    items.forEach(el => el.classList.remove('active'));

                    if (idx >= 0 && idx < items.length) {
                        items[idx].classList.add('active');
                        items[idx].scrollIntoView({
                            block: 'nearest'
                        });
                        activeIndex = idx;
                    }
                };

                const fetchSuggest = async (q) => {
                    const res = await fetch(`${endpoint}?q=${encodeURIComponent(q)}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) return {
                        items: []
                    };
                    return await res.json();
                };

                input.addEventListener('input', () => {
                    const q = input.value.trim();

                    if (t) clearTimeout(t);
                    if (q.length < 2) return closeDropdown();

                    t = setTimeout(async () => {
                        try {
                            const data = await fetchSuggest(q);
                            render(data.items || []);
                        } catch {
                            closeDropdown();
                        }
                    }, 200);
                });

                input.addEventListener('keydown', (e) => {
                    const items = dropdown.querySelectorAll('.sef-search-item');
                    if (dropdown.classList.contains('d-none') || !items.length) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        setActive(Math.min(activeIndex + 1, items.length - 1));
                    }

                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        setActive(Math.max(activeIndex - 1, 0));
                    }

                    if (e.key === 'Enter' && activeIndex >= 0) {
                        e.preventDefault();
                        window.location.href = items[activeIndex].getAttribute('href');
                    }

                    if (e.key === 'Escape') closeDropdown();
                });

                input.addEventListener('focus', () => {
                    if (currentItems.length) openDropdown();
                });

                document.addEventListener('click', (e) => {
                    if (!wrapper.contains(e.target)) closeDropdown();
                });
            });

        })();
    </script>



    <script>
        document.addEventListener('click', function(e) {
            const toggle = e.target.closest('.dropdown-submenu > .dropdown-item.dropdown-toggle');
            if (!toggle) return;

            e.preventDefault();
            e.stopPropagation();

            const submenu = toggle.nextElementSibling;
            if (!submenu || !submenu.classList.contains('dropdown-menu')) return;

            // close other open submenus in same parent
            const parentMenu = toggle.closest('.dropdown-menu');
            parentMenu.querySelectorAll(':scope .dropdown-menu.show').forEach(m => {
                if (m !== submenu) m.classList.remove('show');
            });

            submenu.classList.toggle('show');
        });
    </script>
@endpush
