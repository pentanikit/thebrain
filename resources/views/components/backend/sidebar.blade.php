<aside class="navbar-aside" id="offcanvas_aside">
    <div class="aside-top">
        <a class="brand-wrap" href="{{ route('admindashboard') }}">
            <img class="logo"
                 src="{{ asset('storage/' . site_setting('logo')) }}"
                 alt="Dashboard">
        </a>
        <div>
            <button class="btn btn-icon btn-aside-minimize">
                <i class="text-muted material-icons md-menu_open"></i>
            </button>
        </div>
    </div>

    <nav>
        <ul class="menu-aside">

            {{-- Dashboard --}}
            <li class="menu-item {{ request()->routeIs('admindashboard') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('admindashboard') }}">
                    <i class="icon material-icons md-home"></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>

            {{-- Products + Categories + Add --}}
            @php
                $productMenuActive = request()->routeIs(
                    'adminproducts',
                    'addproducts',
                    'editproduct',
                    'admincategories'
                );
            @endphp

            <li class="menu-item has-submenu {{ $productMenuActive ? 'active' : '' }}">
                <a class="menu-link" href="#">
                    <i class="icon material-icons md-shopping_bag"></i>
                    <span class="text">Products</span>
                </a>

                <div class="submenu" style="{{ $productMenuActive ? 'display:block;' : '' }}">
                    <a href="{{ route('adminproducts') }}"
                       class="{{ request()->routeIs('adminproducts') ? 'active' : '' }}">
                        Products
                    </a>

                    <a href="{{ route('admincategories') }}"
                       class="{{ request()->routeIs('admincategories') ? 'active' : '' }}">
                        Categories
                    </a>

                    <a href="{{ route('addproducts') }}"
                       class="{{ request()->routeIs('addproducts') ? 'active' : '' }}">
                        Add Product
                    </a>
                </div>
            </li>

            {{-- Banners --}}
            <li class="menu-item {{ request()->routeIs('banners') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('banners') }}">
                    <i class="icon material-icons md-image"></i>
                    <span class="text">Banners</span>
                </a>
            </li>

            {{-- Orders --}}
            <li class="menu-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('admin.orders') }}">
                    <i class="icon material-icons md-shopping_cart"></i>
                    <span class="text">Orders</span>
                </a>
            </li>

            {{-- Reviews (placeholder) --}}
            <li class="menu-item">
                <a class="menu-link" href="#">
                    <i class="icon material-icons md-comment"></i>
                    <span class="text">Reviews</span>
                </a>
            </li>

            {{-- Section Titles --}}
            <li class="menu-item {{ request()->routeIs('section-titles.*') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('section-titles.index') }}">
                    <i class="icon material-icons md-pie_chart"></i>
                    <span class="text">Section Titles</span>
                </a>
            </li>
        </ul>

        <hr>

        <ul class="menu-aside">

            {{-- Site Settings --}}
            <li class="menu-item {{ request()->routeIs('site-settings.*') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('site-settings.index') }}">
                    <i class="icon material-icons md-settings"></i>
                    <span class="text">Settings</span>
                </a>
            </li>

            {{-- View Live --}}
            <li class="menu-item">
                <a class="menu-link" href="{{ url('/') }}" target="_blank">
                    <i class="icon material-icons md-local_offer"></i>
                    <span class="text">View Live Page</span>
                </a>
            </li>

        </ul>
    </nav>
</aside>
