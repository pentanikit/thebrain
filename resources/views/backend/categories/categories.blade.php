@extends('backend.layout')
@section('admin')
    <main class="main-wrap">
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
            <div class="content-header">
                <div>
                    <h2 class="content-title card-title">Categories</h2>
                    <p>Add, edit or delete a category</p>
                </div>
                <div>
                    <input class="form-control bg-white" type="text" placeholder="Search Categories">
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <form action="{{ route('admincreatecategories') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                {{-- Name --}}
                                <div class="mb-4">
                                    <label class="form-label" for="name">Name</label>
                                    <input class="form-control" id="name" name="name" type="text"
                                        placeholder="Type here" value="{{ old('name') }}" required>
                                </div>

                                {{-- Slug --}}
                                <div class="mb-4">
                                    <label class="form-label" for="slug">Slug</label>
                                    <input class="form-control" id="slug" name="slug" type="text"
                                        placeholder="Auto from name (editable)" value="{{ old('slug') }}">
                                    <small class="text-muted">Leave empty to auto-generate from name.</small>
                                </div>

                                {{-- Parent --}}
                                <div class="mb-4">
                                    <label class="form-label" for="parent_id">Parent</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="">No parent (Main category)</option>
                                        @foreach (categories() as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('parent_id') == $cat->id)>
                                                {{ str_repeat('— ', max(0, $cat->level - 1)) . $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Choose parent to make this a sub/child category.</small>
                                </div>

                                {{-- Sort order --}}
                                <div class="mb-4">
                                    <label class="form-label" for="sort_order">Sort order</label>
                                    <input class="form-control" id="sort_order" name="sort_order" type="number"
                                        min="0" value="{{ old('sort_order', 0) }}">
                                    <small class="text-muted">Lower number will appear first.</small>
                                </div>

                                {{-- Active toggle --}}
                                <div class="mb-4 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>

                                {{-- Thumbnail --}}
                                <div class="mb-4">
                                    <label class="form-label" for="thumbnail">Thumbnail</label>
                                    <input class="form-control" type="file" id="thumbnail" name="thumbnail">
                                    <small class="text-muted">Optional image for the category.</small>
                                </div>



                                <div class="d-grid">
                                    <button class="btn btn-primary" type="submit">Create category</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-9">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="">
                                                </div>
                                            </th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            {{-- <th>Description</th> --}}
                                            <th>Slug</th>
                                            <th>Order</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                        <tbody>
                                            @forelse (categories() as $category)
                                                <tr>
                                                    <td class="text-center">
                                                        @if($category->level > 1)
                                                            {{-- Child / subcategory indicator --}}
                                                            <i class="material-icons md-subdirectory_arrow_right text-muted"></i>
                                                        @else
                                                            {{-- Main category checkbox --}}
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" value="{{ $category->id }}">
                                                            </div>
                                                        @endif
                                                    </td>

                                                    {{-- ID --}}
                                                    <td>{{ $category->id }}</td>

                                                    {{-- Name (with visual indent based on level) --}}
                                                    <td>
                                                        <b>
                                                            {!! str_repeat('— ', max(0, $category->level - 1)) !!}
                                                            {{ $category->name }}
                                                        </b>
                                                    </td>

                                                    {{-- Description (no column in schema, so just mirror name) --}}
                                                    {{-- <td>{{ $category->name }}</td> --}}

                                                    {{-- Slug (styled with leading slash like your sample) --}}
                                                    <td>/{{ $category->slug }}</td>

                                                    {{-- Sort order --}}
                                                    <td>{{ $category->sort_order }}</td>

                                                    {{-- Action dropdown --}}
                                                    <td class="text-end">
                                                        <div class="dropdown">
                                                            <a class="btn btn-light rounded btn-sm font-sm"
                                                            href="#"
                                                            data-bs-toggle="dropdown">
                                                                <i class="material-icons md-more_horiz"></i>
                                                            </a>
                                                            <div class="dropdown-menu">
                                                                {{-- Update these routes if you named them differently --}}
                                                                <a class="dropdown-item"
                                                                href="">
                                                                    Edit info
                                                                </a>

                                                                <form action=""
                                                                    method=""
                                                                    onsubmit="return confirm('Delete this category?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>No Categories Found</tr>
                                            @endforelse
                                        </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <footer class="main-footer font-xs">
            <div class="row pb-30 pt-15">
                <div class="col-sm-6">
                    <script>
                        document.write(new Date().getFullYear())
                    </script> &copy;, Ecom - HTML Ecommerce Template .
                </div>
                <div class="col-sm-6">
                    <div class="text-sm-end">All rights reserved</div>
                </div>
            </div>
        </footer>
    </main>
@endsection
@push('scripts')
<script>
// Auto-generate slug from name if slug is empty / untouched
document.getElementById('name').addEventListener('input', function () {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value) {
        slugInput.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }
});
</script>
@endpush
