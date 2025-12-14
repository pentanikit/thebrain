@extends('backend.layout')
@section('admin')
    <main class="main-wrap">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        <header class="main-header navbar">
            <div class="col-search">
                <form class="searchform">
                    <div class="input-group">
                        <input class="form-control" list="search_terms" type="text" placeholder="Search term">
                        <button class="btn btn-light bg" type="button"><i class="material-icons md-search"></i></button>
                    </div>
                    <datalist id="search_terms">
                        <option value="Products"></option>
                        <option value="New orders"></option>
                        <option value="Apple iphone"></option>
                        <option value="Ahmed Hassan"></option>
                    </datalist>
                </form>
            </div>
            <div class="col-nav">
                <button class="btn btn-icon btn-mobile me-auto" data-trigger="#offcanvas_aside"><i
                        class="material-icons md-apps"></i></button>
                <ul class="nav">
                    <li class="nav-item"><a class="nav-link btn-icon" href="#"><i
                                class="material-icons md-notifications animation-shake"></i><span
                                class="badge rounded-pill">3</span></a></li>
                    <li class="nav-item"><a class="nav-link btn-icon darkmode" href="#"><i
                                class="material-icons md-nights_stay"></i></a></li>
                    <li class="nav-item"><a class="requestfullscreen nav-link btn-icon" href="#"><i
                                class="material-icons md-cast"></i></a></li>
                    <li class="dropdown nav-item"><a class="dropdown-toggle" id="dropdownLanguage" data-bs-toggle="dropdown"
                            href="#" aria-expanded="false"><i class="material-icons md-public"></i></a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLanguage"><a
                                class="dropdown-item text-brand" href="#"><img src="assets/imgs/theme/flag-us.png"
                                    alt="English">English</a><a class="dropdown-item" href="#"><img
                                    src="assets/imgs/theme/flag-fr.png" alt="Français">Fran&ccedil;ais</a><a
                                class="dropdown-item" href="#"><img src="assets/imgs/theme/flag-jp.png"
                                    alt="Français">&#x65E5;&#x672C;&#x8A9E;</a><a class="dropdown-item" href="#"><img
                                    src="assets/imgs/theme/flag-cn.png" alt="Français">&#x4E2D;&#x56FD;&#x4EBA;</a></div>
                    </li>
                    <li class="dropdown nav-item"><a class="dropdown-toggle" id="dropdownAccount" data-bs-toggle="dropdown"
                            href="#" aria-expanded="false"><img class="img-xs rounded-circle"
                                src="assets/imgs/people/avatar2.jpg" alt="User"></a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownAccount"><a
                                class="dropdown-item" href="#"><i class="material-icons md-perm_identity"></i>Edit
                                Profile</a><a class="dropdown-item" href="#"><i
                                    class="material-icons md-settings"></i>Account Settings</a><a class="dropdown-item"
                                href="#"><i class="material-icons md-account_balance_wallet"></i>Wallet</a><a
                                class="dropdown-item" href="#"><i class="material-icons md-receipt"></i>Billing</a><a
                                class="dropdown-item" href="#"><i class="material-icons md-help_outline"></i>Help
                                center</a>
                            <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#"><i
                                    class="material-icons md-exit_to_app"></i>Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </header>
        <section class="content-main">
            <div class="content-header">
                <div>
                    <h2 class="content-title card-title">All Products</h2>
                   
                </div>
                <div class="gap-16">
                    <a class="btn btn-light rounded font-md" href="#">Export</a>
                    <a class="btn btn-light rounded font-md" href="#">Import</a>
                    <a class="btn btn-primary btn-sm rounded" href="{{ route('addproducts') }}">Create new</a>
                </div>
            </div>
            <div class="card mb-4">
                <header class="card-header">
                    <form method="GET" action="{{ route('adminproducts') }}">
                        <div class="row gx-3 align-items-center">
                            {{-- Search --}}
                            <div class="col-lg-4 col-md-6 me-auto">
                                <input class="form-control" type="text" name="q" value="{{ request('q') }}"
                                    placeholder="Search by name or SKU...">
                            </div>

                            {{-- Category filter --}}
                            <div class="col-lg-2 col-6 col-md-3">
                                <select name="category_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All category</option>
                                    @foreach (categories() as $category)
                                        <option value="{{ $category->id }}"
                                            {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Sort --}}
                            <div class="col-lg-2 col-6 col-md-3">
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="latest"
                                        {{ request('sort') === 'latest' || !request('sort') ? 'selected' : '' }}>Latest
                                        added</option>
                                    <option value="cheap" {{ request('sort') === 'cheap' ? 'selected' : '' }}>Cheap
                                        first</option>
                                    <option value="expensive"{{ request('sort') === 'expensive' ? 'selected' : '' }}>
                                        Expensive first</option>
                                </select>
                            </div>

                            {{-- Optional explicit submit button (for search) --}}
                            <div class="col-lg-2 col-md-3 mt-2 mt-md-0">
                                <button type="submit" class="btn btn-primary w-100">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </header>
                <!-- card-header end//-->
                <div class="card-body">
                    <div class="row gx-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 row-cols-xxl-5">
                        @forelse ($products as $item)
                            <x-backend.admin-product-card :product="$item" />
                        @empty
                            <p>No Products Found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="pagination-area mt-30 mb-50">
                {{ $products->links() }}
                {{-- or just: {{ $products->links() }} --}}
            </div>
        </section>

    </main>
@endsection
