@extends('backend.layout')

@section('admin')
<main class="main-wrap">
    <header class="main-header navbar">
        <div class="col-search">
            <h4 class="mb-0">Hero Banner Settings</h4>
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

    <section class="content-main">
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Upload Home Page Banners</h2>
                <p>Upload up to 4 hero banners for your homepage.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                {{-- Change the action route according to your setup --}}
                <form action="{{ route('bannerupload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                   

                    @php
                        // Expecting $banners = ['banner_1' => 'path/to/img.jpg', ...] from controller
                        $banner1 = $banners['banner_1'] ?? null;
                        $banner2 = $banners['banner_2'] ?? null;
                        $banner3 = $banners['banner_3'] ?? null;
                        $banner4 = $banners['banner_4'] ?? null;
                    @endphp

                    <div class="row g-4">

                        {{-- Banner 1 --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Banner 1</label>
                            <div class="mb-2">
                                <div class="ratio ratio-16x9 border rounded d-flex align-items-center justify-content-center bg-light overflow-hidden">
                                    @if ($banner1)
                                        <img id="preview-banner-1"
                                             src="{{ asset('storage').'/'.$banner1 }}"
                                             alt="Banner 1"
                                             class="img-fluid">
                                    @else
                                        <span id="placeholder-banner-1" class="text-muted">No image selected</span>
                                        <img id="preview-banner-1"
                                             src=""
                                             alt="Banner 1"
                                             class="img-fluid d-none">
                                    @endif
                                </div>
                            </div>
                            <input type="file"
                                   class="form-control"
                                   name="banner_1"
                                   id="banner_1"
                                   accept="image/*"
                                   onchange="previewImage(this, 'preview-banner-1', 'placeholder-banner-1')">
                            <small class="text-muted">Recommended size: 1920x600px (JPG/PNG).</small>
                        </div>

                        {{-- Banner 2 --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Banner 2</label>
                            <div class="mb-2">
                                <div class="ratio ratio-16x9 border rounded d-flex align-items-center justify-content-center bg-light overflow-hidden">
                                    @if ($banner2)
                                        <img id="preview-banner-2"
                                             src="{{ asset('storage').'/'.$banner2 }}"
                                             alt="Banner 2"
                                             class="img-fluid">
                                    @else
                                        <span id="placeholder-banner-2" class="text-muted">No image selected</span>
                                        <img id="preview-banner-2"
                                             src=""
                                             alt="Banner 2"
                                             class="img-fluid d-none">
                                    @endif
                                </div>
                            </div>
                            <input type="file"
                                   class="form-control"
                                   name="banner_2"
                                   id="banner_2"
                                   accept="image/*"
                                   onchange="previewImage(this, 'preview-banner-2', 'placeholder-banner-2')">
                            <small class="text-muted">Optional secondary hero banner.</small>
                        </div>

                        {{-- Banner 3 --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Banner 3</label>
                            <div class="mb-2">
                                <div class="ratio ratio-16x9 border rounded d-flex align-items-center justify-content-center bg-light overflow-hidden">
                                    @if ($banner3)
                                        <img id="preview-banner-3"
                                             src="{{ asset('storage').'/'.$banner3 }}"
                                             alt="Banner 3"
                                             class="img-fluid">
                                    @else
                                        <span id="placeholder-banner-3" class="text-muted">No image selected</span>
                                        <img id="preview-banner-3"
                                             src=""
                                             alt="Banner 3"
                                             class="img-fluid d-none">
                                    @endif
                                </div>
                            </div>
                            <input type="file"
                                   class="form-control"
                                   name="banner_3"
                                   id="banner_3"
                                   accept="image/*"
                                   onchange="previewImage(this, 'preview-banner-3', 'placeholder-banner-3')">
                            <small class="text-muted">Optional promotional banner.</small>
                        </div>

                        {{-- Banner 4 --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Banner 4</label>
                            <div class="mb-2">
                                <div class="ratio ratio-16x9 border rounded d-flex align-items-center justify-content-center bg-light overflow-hidden">
                                    @if ($banner4)
                                        <img id="preview-banner-4"
                                             src="{{ asset('storage').'/'.$banner4 }}"
                                             alt="Banner 4"
                                             class="img-fluid">
                                    @else
                                        <span id="placeholder-banner-4" class="text-muted">No image selected</span>
                                        <img id="preview-banner-4"
                                             src=""
                                             alt="Banner 4"
                                             class="img-fluid d-none">
                                    @endif
                                </div>
                            </div>
                            <input type="file"
                                   class="form-control"
                                   name="banner_4"
                                   id="banner_4"
                                   accept="image/*"
                                   onchange="previewImage(this, 'preview-banner-4', 'placeholder-banner-4')">
                            <small class="text-muted">Optional seasonal/offer banner.</small>
                        </div>

                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            Save Banners
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </section>
</main>

{{-- Inline script so you don't depend on @push/@stack --}}
<script>
    function previewImage(input, previewId, placeholderId) {
        const file = input.files && input.files[0];
        const previewImg = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);

        if (!file || !previewImg) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewImg.classList.remove('d-none');
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(file);
    }
</script>
@endsection
