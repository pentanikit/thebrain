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
          <button class="btn btn-icon btn-mobile me-auto" data-trigger="#offcanvas_aside"><i class="material-icons md-apps"></i></button>
          <ul class="nav">
            <li class="nav-item"><a class="nav-link btn-icon" href="#"><i class="material-icons md-notifications animation-shake"></i><span class="badge rounded-pill">3</span></a></li>
            <li class="nav-item"><a class="nav-link btn-icon darkmode" href="#"><i class="material-icons md-nights_stay"></i></a></li>
            <li class="nav-item"><a class="requestfullscreen nav-link btn-icon" href="#"><i class="material-icons md-cast"></i></a></li>
            <li class="dropdown nav-item"><a class="dropdown-toggle" id="dropdownLanguage" data-bs-toggle="dropdown" href="#" aria-expanded="false"><i class="material-icons md-public"></i></a>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLanguage">
                  <a class="dropdown-item text-brand" href="#"><img src="assets/imgs/theme/flag-us.png" alt="English">English</a>
                  <a class="dropdown-item" href="#"><img src="assets/imgs/theme/flag-fr.png" alt="Français">Français</a>
                  <a class="dropdown-item" href="#"><img src="assets/imgs/theme/flag-jp.png" alt="日本語">日本語</a>
                  <a class="dropdown-item" href="#"><img src="assets/imgs/theme/flag-cn.png" alt="中国人">中国人</a>
              </div>
            </li>
            <li class="dropdown nav-item"><a class="dropdown-toggle" id="dropdownAccount" data-bs-toggle="dropdown" href="#" aria-expanded="false"><img class="img-xs rounded-circle" src="assets/imgs/people/avatar2.jpg" alt="User"></a>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownAccount">
                  <a class="dropdown-item" href="#"><i class="material-icons md-perm_identity"></i>Edit Profile</a>
                  <a class="dropdown-item" href="#"><i class="material-icons md-settings"></i>Account Settings</a>
                  <a class="dropdown-item" href="#"><i class="material-icons md-account_balance_wallet"></i>Wallet</a>
                  <a class="dropdown-item" href="#"><i class="material-icons md-receipt"></i>Billing</a>
                  <a class="dropdown-item" href="#"><i class="material-icons md-help_outline"></i>Help center</a>
                <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#"><i class="material-icons md-exit_to_app"></i>Logout</a>
              </div>
            </li>
          </ul>
        </div>
      </header>

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
      @php
          // for stock status
          $stockStatus = old('stock_status', $product->stock_status ?? 'in_stock');
          // for category selects
          $oldCategoryId      = old('category_id', $product->category_id ?? null);
          $oldSubCategoryId   = old('sub_category_id', $product->sub_category_id ?? null);
          $oldChildCategoryId = old('child_category_id', $product->child_category_id ?? null);
          $parentCategories   = categories();
      @endphp

      <section class="content-main">
          <form id="productForm"
                action="{{ route('updateproduct', $product->id) }}" {{-- change route name if needed --}}
                method="POST"
                enctype="multipart/form-data">
              @csrf
              

              <div class="row">
                  <div class="col-9">
                      <div class="content-header d-flex justify-content-between align-items-center">
                          <h2 class="content-title">Edit Product</h2>
                      </div>
                  </div>

                  {{-- LEFT SIDE --}}
                  <div class="col-lg-6">
                      {{-- BASIC INFO --}}
                      <div class="card mb-4">
                          <div class="card-header">
                              <h4>Basic</h4>
                          </div>
                          <div class="card-body">
                              {{-- Product Name --}}
                              <div class="mb-4">
                                  <label class="form-label" for="name">Product title</label>
                                  <input  class="form-control @error('name') is-invalid @enderror"
                                          id="name"
                                          name="name"
                                          type="text"
                                          value="{{ old('name', $product->name ?? '') }}"
                                          placeholder="Type here">
                                  @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                              </div>

                              {{-- Slug --}}
                              <div class="mb-4">
                                  <label class="form-label" for="slug">Slug</label>
                                  <input  class="form-control @error('slug') is-invalid @enderror"
                                          id="slug"
                                          name="slug"
                                          type="text"
                                          value="{{ old('slug', $product->slug ?? '') }}"
                                          placeholder="Auto from title (editable)">
                                  <small class="text-muted">Leave empty to auto-generate from title.</small>
                                  @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                              </div>

                              {{-- SKU --}}
                              <div class="mb-4">
                                  <label class="form-label" for="sku">SKU</label>
                                  <input  class="form-control @error('sku') is-invalid @enderror"
                                          id="sku"
                                          name="sku"
                                          type="text"
                                          value="{{ old('sku', $product->sku ?? '') }}"
                                          placeholder="Optional product code">
                                  @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                              </div>

                              {{-- Full Description --}}
                              <div class="mb-4">
                                  <label class="form-label" for="description">Full description</label>
                                  <textarea class="form-control @error('description') is-invalid @enderror"
                                            id="description"
                                            name="description"
                                            rows="4"
                                            placeholder="Type product details here...">{{ old('description', optional($product->descriptions)->body ?? $product->descriptions[0]->body ?? '') }}</textarea>
                                  @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                              </div>

                              {{-- Pricing --}}
                              <div class="row">
                                  <div class="col-lg-4">
                                      <div class="mb-4">
                                          <label class="form-label" for="price">Regular price</label>
                                          <input  class="form-control @error('price') is-invalid @enderror"
                                                  id="price"
                                                  name="price"
                                                  value="{{ old('price', $product->price ?? '') }}"
                                                  placeholder="BDT"
                                                  type="number"
                                                  min="0">
                                          @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                      </div>
                                  </div>
                                  <div class="col-lg-4">
                                      <div class="mb-4">
                                          <label class="form-label" for="old_price">Old price</label>
                                          <input  class="form-control @error('old_price') is-invalid @enderror"
                                                  id="old_price"
                                                  name="old_price"
                                                  value="{{ old('old_price', $product->old_price ?? '') }}"
                                                  placeholder="BDT"
                                                  type="number"
                                                  min="0">
                                          @error('old_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                      </div>
                                  </div>
                                  <div class="col-lg-4">
                                      <div class="mb-4">
                                          <label class="form-label" for="offer_price">Offer price</label>
                                          <input  class="form-control @error('offer_price') is-invalid @enderror"
                                                  id="offer_price"
                                                  name="offer_price"
                                                  value="{{ old('offer_price', $product->offer_price ?? '') }}"
                                                  placeholder="BDT"
                                                  type="number"
                                                  min="0">
                                          @error('offer_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                      </div>
                                  </div>
                              </div>

                              {{-- Stock --}}
                              <div class="row">
                                  <div class="col-lg-6">
                                      <div class="mb-4">
                                          <label class="form-label" for="stock_quantity">Stock quantity</label>
                                          <input  class="form-control @error('stock_quantity') is-invalid @enderror"
                                                  id="stock_quantity"
                                                  name="stock_quantity"
                                                  type="number"
                                                  min="0"
                                                  value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">
                                          @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                      </div>
                                  </div>
                                  <div class="col-lg-6">
                                      <div class="mb-4">
                                          <label class="form-label" for="stock_status">Stock status</label>
                                          <select class="form-select @error('stock_status') is-invalid @enderror"
                                                  id="stock_status"
                                                  name="stock_status">
                                              <option value="in_stock"    {{ $stockStatus === 'in_stock' ? 'selected' : '' }}>In stock</option>
                                              <option value="out_of_stock" {{ $stockStatus === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                                              <option value="preorder"    {{ $stockStatus === 'preorder' ? 'selected' : '' }}>Pre-order</option>
                                          </select>
                                          @error('stock_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                      </div>
                                  </div>
                              </div>

                              {{-- Active / Draft note (logic via hidden action + JS same as add) --}}
                              <div class="form-check mb-2">
                                  <input class="form-check-input"
                                        type="checkbox"
                                        id="activeCheckbox"
                                        checked
                                        disabled>
                                  <label class="form-check-label" for="activeCheckbox">
                                      Update product as publish/draft using buttons below
                                  </label>
                              </div>
                              <small class="text-muted">
                                  “Publish” button will save as <strong>active</strong>, “Save to draft” as <strong>inactive</strong>.
                              </small>
                          </div>
                      </div>

                      {{-- SPECIFICATIONS (keep same as your add form for now) --}}
                    {{-- SPECIFICATIONS --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Specifications</h4>
                            <button type="button" id="addSpecRow" class="btn btn-sm btn-outline-primary">
                                + Add row
                            </button>
                        </div>
                        <div class="card-body">
                            @php
                                $specRows = [];

                                // 1) If validation failed and came back, use old() from the form
                                if (old('spec_key')) {
                                    $keys = old('spec_key');
                                    $vals = old('spec_value');

                                    foreach ($keys as $idx => $k) {
                                        $specRows[] = [
                                            'key'   => $k,
                                            'value' => $vals[$idx] ?? '',
                                        ];
                                    }
                                }
                                // 2) Otherwise, use DB JSON from $product->specification->specs
                                elseif ($product->relationLoaded('specifications') && $product->specifications) {
                                    $specs = $product->specifications; // because of $casts = ['specs' => 'array']

                                    // if not casted for any reason, make sure it's array
                                    if (is_string($specs)) {
                                        $specs = json_decode($specs, true) ?: [];
                                    }

                                    if (is_array($specs)) {
                                        foreach ($specs as $item) {
                                            $specRows[] = [
                                                'key'   => $item['key']   ?? '',
                                                'value' => $item['value'] ?? '',
                                            ];
                                        }
                                    }
                                }

                                // 3) If still empty (no specs + no old input), show one blank row
                                if (!count($specRows)) {
                                    $specRows[] = ['key' => '', 'value' => ''];
                                }
                            @endphp

                            <div id="specRows">
                                @foreach ($specRows as $row)
                                    <div class="row g-2 align-items-center mb-2 spec-row">
                                        <div class="col-md-4">
                                            <input type="text"
                                                name="spec_key[]"
                                                class="form-control"
                                                placeholder="Key (e.g. gsm)"
                                                value="{{ $row['key'] }}">
                                        </div>
                                        <div class="col-md-7">
                                            <input type="text"
                                                name="spec_value[]"
                                                class="form-control"
                                                placeholder="Value (e.g. 80)"
                                                value="{{ $row['value'] }}">
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger removeSpecRow">&times;</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <small class="text-muted">
                                These will be stored as a JSON array like:
                                <code>[{"key":"gsm","value":"80"}]</code>
                            </small>
                        </div>
                    </div>

                  </div>

                  {{-- RIGHT SIDE --}}
                  <div class="col-lg-3 offset-lg-1">
                      {{-- MEDIA --}}
                      <div class="card mb-4">
                          <div class="card-header">
                              <h4>Media</h4>
                          </div>
                          <div class="card-body">
                              {{-- Thumbnail --}}
                              <div class="mb-3">
                                  <label class="form-label" for="thumbnail">Thumbnail (main image)</label>

                                  @if(!empty($product->thumbnail))
                                      <div class="mb-2">
                                          <small class="text-muted d-block">Current thumbnail:</small>
                                          <img src="{{ asset('storage/'.$product->thumbnail) }}"
                                               alt="Current thumbnail"
                                               class="img-thumbnail"
                                               style="max-height: 120px;">
                                      </div>
                                  @endif

                                  <div class="input-upload">
                                      <img src="{{ asset('assets/imgs/theme/upload.svg') }}" alt="">
                                      <input  class="form-control @error('thumbnail') is-invalid @enderror"
                                              id="thumbnail"
                                              name="thumbnail"
                                              type="file"
                                              accept="image/*">
                                      @error('thumbnail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                  </div>
                                  <div id="thumbnailPreview" class="mt-2"></div>
                                  <small class="text-muted d-block">Leave empty to keep existing thumbnail.</small>
                              </div>

                              {{-- Gallery images --}}
                            <div class="mb-3">
                                <label class="form-label" for="images">Gallery images</label>

                                @if($product->relationLoaded('images') && $product->images->count())
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach($product->images as $img)
                                            <img src="{{ asset('storage/'.$img->path) }}"
                                                alt="Gallery image"
                                                class="img-thumbnail"
                                                style="height: 70px;">
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block mb-1">Existing gallery images shown above.</small>
                                @endif

                                <input  class="form-control @error('images.*') is-invalid @enderror"
                                        id="images"
                                        name="images[]"
                                        type="file"
                                        multiple
                                        accept="image/*">
                                @error('images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                                <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                <small class="text-muted">You can select multiple images. New uploads will be handled in update().</small>
                            </div>

                          </div>
                      </div>

                      {{-- ORGANIZATION --}}
                      <div class="card mb-4">
                          <div class="card-header">
                              <h4>Organization</h4>
                          </div>
                          <div class="card-body">
                              <div class="row gx-2">
                                  {{-- Category (parent) --}}
                                  <div class="col-12 mb-3">
                                      <label class="form-label" for="category_id">Category</label>
                                      <select class="form-select @error('category_id') is-invalid @enderror"
                                              id="category_id"
                                              name="category_id"
                                              data-old="{{ $oldCategoryId }}">
                                          <option value="">Select category</option>
                                          @foreach ($parentCategories as $cat)
                                              <option value="{{ $cat->id }}"
                                                  {{ (string)$oldCategoryId === (string)$cat->id ? 'selected' : '' }}>
                                                  {{ $cat->name }}
                                              </option>
                                          @endforeach
                                      </select>
                                      @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                  </div>

                                  {{-- Sub-category --}}
                                  <div class="col-12 mb-3">
                                      <label class="form-label" for="sub_category_id">Sub-category</label>
                                      <select class="form-select @error('sub_category_id') is-invalid @enderror"
                                              id="sub_category_id"
                                              name="sub_category_id"
                                              data-old="{{ $oldSubCategoryId }}">
                                          <option value="">Select sub-category (optional)</option>
                                          {{-- options will be loaded by JS --}}
                                      </select>
                                      @error('sub_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                  </div>

                                  {{-- Child-category --}}
                                  <div class="col-12 mb-3">
                                      <label class="form-label" for="child_category_id">Child-category</label>
                                      <select class="form-select @error('child_category_id') is-invalid @enderror"
                                              id="child_category_id"
                                              name="child_category_id"
                                              data-old="{{ $oldChildCategoryId }}">
                                          <option value="">Select child-category (optional)</option>
                                          {{-- options will be loaded by JS --}}
                                      </select>
                                      @error('child_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div> {{-- /RIGHT --}}

                  {{-- ACTION BUTTONS --}}
                  <div>
                      <input type="hidden" name="action" id="formAction" value="publish">

                      <button type="submit"
                              id="draftBtn"
                              class="btn btn-light rounded font-sm mr-5 text-body hover-up">
                            Save to draft
                      </button>

                      <button type="submit"
                              id="publishBtn"
                              class="btn btn-md rounded font-sm hover-up">
                        <span id="publishSpinner"
                              class="spinner-border spinner-border-sm me-2 d-none"
                              role="status"
                              aria-hidden="true"></span>
                            Publish
                      </button>
                  </div>
              </div>
          </form>
      </section>

    </main>
@endsection
@push('scripts')
<script>
    // === Auto slug from title ===
    document.getElementById('name').addEventListener('input', function () {
        const slugInput = document.getElementById('slug');
        if (slugInput.value.trim() === '') {
            slugInput.value = this.value
                .toLowerCase()
                .replace(/[\s\W-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });

    // === Handle Draft / Publish button action ===
    document.getElementById('draftBtn').addEventListener('click', function () {
        document.getElementById('formAction').value = 'draft';
    });

    document.getElementById('publishBtn').addEventListener('click', function () {
        document.getElementById('formAction').value = 'publish';
    });

    // === Spinner on publish ===
    document.getElementById('productForm').addEventListener('submit', function (e) {
        const action = document.getElementById('formAction').value;
        if (action === 'publish') {
            const publishBtn = document.getElementById('publishBtn');
            const spinner = document.getElementById('publishSpinner');
            publishBtn.disabled = true;
            spinner.classList.remove('d-none');
        }
    });

    // === Thumbnail preview ===
    document.getElementById('thumbnail').addEventListener('change', function (e) {
        const container = document.getElementById('thumbnailPreview');
        container.innerHTML = '';
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.classList.add('img-thumbnail');
            img.style.maxWidth = '120px';
            img.style.maxHeight = '120px';
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });

    // === Multiple images preview ===
    document.getElementById('images').addEventListener('change', function (e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';

        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (event) {
                const wrapper = document.createElement('div');
                wrapper.classList.add('position-relative');

                const img = document.createElement('img');
                img.src = event.target.result;
                img.classList.add('img-thumbnail', 'me-2', 'mb-2');
                img.style.maxWidth = '90px';
                img.style.maxHeight = '90px';

                wrapper.appendChild(img);
                preview.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    });

    // === Specification row add/remove ===
    document.getElementById('addSpecRow').addEventListener('click', function () {
        const container = document.getElementById('specRows');
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-center mb-2 spec-row';
        row.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="spec_key[]" class="form-control" placeholder="Key (e.g. Screen Size)">
            </div>
            <div class="col-md-7">
                <input type="text" name="spec_value[]" class="form-control" placeholder="Value (e.g. 43 inch)">
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger removeSpecRow">&times;</button>
            </div>
        `;
        container.appendChild(row);
    });

    document.getElementById('specRows').addEventListener('click', function (e) {
        if (e.target.classList.contains('removeSpecRow')) {
            const row = e.target.closest('.spec-row');
            row.remove();
        }
    });
</script>
@endpush

@push('scripts')
<script>
    const categoryChildrenBaseUrl = "{{ url('/admin/categories') }}";

    const categorySelect     = document.getElementById('category_id');
    const subCategorySelect  = document.getElementById('sub_category_id');
    const childCategorySelect= document.getElementById('child_category_id');

    const oldCategoryId      = categorySelect.dataset.old || '';
    const oldSubCategoryId   = subCategorySelect.dataset.old || '';
    const oldChildCategoryId = childCategorySelect.dataset.old || '';

    function resetSelect(select, placeholderText) {
        select.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholderText;
        select.appendChild(opt);
    }

    function loadChildren(parentId, selectElement, placeholder, selectedId = null) {
        resetSelect(selectElement, placeholder);

        if (!parentId) {
            return Promise.resolve(); // nothing to load
        }

        return fetch(`${categoryChildrenBaseUrl}/${parentId}/children`)
            .then(response => response.json())
            .then(data => {
                data.forEach(child => {
                    const opt = document.createElement('option');
                    opt.value = child.id;
                    opt.textContent = child.name;
                    if (selectedId && String(selectedId) === String(child.id)) {
                        opt.selected = true;
                    }
                    selectElement.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Failed to load categories:', err);
            });
    }

    // When category changes -> update sub + child
    categorySelect.addEventListener('change', function () {
        const parentId = this.value;

        // load sub-categories from parent
        loadChildren(parentId, subCategorySelect, 'Select sub-category (optional)')
            .then(() => {
                // by default child from parent (if no sub selected yet)
                loadChildren(parentId, childCategorySelect, 'Select child-category (optional)');
            });
    });

    // When sub-category changes -> child from sub OR parent
    subCategorySelect.addEventListener('change', function () {
        const subId    = this.value;
        const parentId = categorySelect.value;

        const baseId = subId || parentId; // if no sub, use parent
        loadChildren(baseId, childCategorySelect, 'Select child-category (optional)');
    });

    // ===== Restore old values after validation error =====
    document.addEventListener('DOMContentLoaded', function () {
        if (oldCategoryId) {
            // first load sub-categories; preselect oldSub
            loadChildren(oldCategoryId, subCategorySelect, 'Select sub-category (optional)', oldSubCategoryId)
                .then(() => {
                    const baseId = oldSubCategoryId || oldCategoryId;
                    if (baseId) {
                        return loadChildren(baseId, childCategorySelect, 'Select child-category (optional)', oldChildCategoryId);
                    }
                })
                .catch(err => console.error(err));
        }
    });
</script>
@endpush