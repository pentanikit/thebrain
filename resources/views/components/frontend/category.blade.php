        <!-- CATEGORIES -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <div class="category-section-title">Shop by Category</div>
                    {{-- <h3 class="h5 fw-semibold mt-1 mb-0">Everything for your home in one place</h3> --}}
                </div>
                <div class="pill-chip">
                    <span class="pill-dot"></span> Budget friendly collections
                </div>
            </div>

            <div class="row category-row flex-md-wrap g-3">

                @forelse (categories() as $item)
                    <div class="col-6 col-md-3">
                        <div class="category-card text-center p-3 h-100">
                            <img src="{{ asset('storage').'/'.$item->thumbnail }}" class="img-fluid mb-3"
                                alt="{{ $item->name }}">
                            <div class="category-name">{{ $item->name }}</div>
                            {{-- <small class="text-muted">24&quot; – 100&quot; smart TVs</small> --}}
                        </div>
                    </div>
                @empty
                    <div>Categories will go here</div>
                @endforelse

            </div>
        </section>
