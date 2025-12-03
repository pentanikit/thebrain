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
                    <input class="form-control bg-white" type="text" placeholder="Search Categories" id="categorySearch"
                        autocomplete="off" />

                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <form id="categoryCreateForm"
                                action="{{ route('admincreatecategories') }}"
                                method="POST"
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
                                    <input class="form-control" type="file" id="thumbnail" name="thumbnail" accept="image/*">
                                    <small class="text-muted">Optional image for the category.</small>

                                    {{-- Preview --}}
                                    <div class="mt-2">
                                        <img id="thumbnailPreview"
                                            src="#"
                                            alt="Thumbnail preview"
                                            class="img-thumbnail d-none"
                                            style="max-height: 120px;">
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                            id="createCategoryBtn"
                                            type="submit">
                                        <span class="btn-text">Create category</span>
                                        <span class="spinner-border spinner-border-sm ms-2 d-none"
                                            role="status"
                                            aria-hidden="true"></span>
                                    </button>
                                </div>
                            </form>

                        </div>
                        <div class="col-md-9">
                            <div class="table-responsive">
                                <?php $counter = 1; ?>
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
                                            <th>Image</th>

                                            <th>Slug</th>
                                            <th>Order</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoryTableBody">
                                        @include('backend.categories._rows', ['categories' => categories()])

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

// Live category search with debounce
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('categorySearch');
    const tableBody   = document.getElementById('categoryTableBody');
    let timer = null;

    if (!searchInput || !tableBody) return;

    searchInput.addEventListener('keyup', function () {
        clearTimeout(timer);

        timer = setTimeout(function () {
            const query = searchInput.value.trim();

            fetch("{{ route('categorysearch') }}?q=" + encodeURIComponent(query), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    tableBody.innerHTML = html;
                })
                .catch(err => {
                    console.error('Search error:', err);
                });
        }, 300); // 300ms delay after user stops typing
    });
});



// Spinner on submit + prevent double submit
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('categoryCreateForm');
    const btn  = document.getElementById('createCategoryBtn');

    if (form && btn) {
        const btnText  = btn.querySelector('.btn-text');
        const spinner  = btn.querySelector('.spinner-border');

        form.addEventListener('submit', function () {
            btn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            if (btnText) btnText.textContent = 'Creating...';
        });
    }

    // Image preview
    const fileInput = document.getElementById('thumbnail');
    const preview   = document.getElementById('thumbnailPreview');

    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (!file) {
                preview.src = '#';
                preview.classList.add('d-none');
                return;
            }

            if (!file.type.startsWith('image/')) {
                // Not an image – reset & optionally show a warning
                preview.src = '#';
                preview.classList.add('d-none');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
});




</script>
@endpush

