
        <!-- CATEGORIES -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <div>
                    <div class="category-section-title">Shop by Category</div>
                    
                </div>
                <div class="pill-chip">
                    <span class="pill-dot"></span> Budget friendly collections
                </div>
            </div>
            {{-- <p class="category-scroll-hint d-sm-none mb-1">
                Swipe left/right to explore categories →
            </p> --}}

            <!-- 8 categories – all in one horizontal row -->
            <div class="category-row">
                @forelse (categories() as $item)
                    <div class="category-item">
                        <a style="color:black; text-decoration: none;" href="{{ route('productfilter', $item->name) }}">
                        <div class="category-card text-center p-3 h-100">
                            <img src="{{ asset('storage') . '/' . $item->thumbnail }}" class="img-fluid mb-3"
                                alt="{{ $item->name }}">
                            <div class="category-name">{{ $item->name }}</div>
                            {{-- <small class="text-muted">24&quot; – 100&quot; smart TVs</small> --}}
                        </div>
                        </a>

                    </div>

                @empty
                    <div>Categories will go here</div>
                @endforelse



            </div>
        </section>
