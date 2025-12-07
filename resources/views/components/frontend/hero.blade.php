<!-- HERO -->
<section class="mb-4 hero-compact">
    <div class="row g-3 align-items-stretch hero-compact-row">
        <!-- Left big banner -->
        <div class="col-lg-6 d-flex">
            <div class="hero-card hero-card-main w-100">
                @if (banner_url('banner_1'))
                    <img src="{{ banner_url('banner_1') }}" alt="Hero Banner 1" class="hero-card-img">
                @else
                    <div class="hero-placeholder">No banner uploaded</div>
                @endif
            </div>
        </div>

        <!-- Right side banners -->
        <div class="col-lg-6 d-flex flex-column gap-3">
            <!-- Top banner -->
            <div class="hero-card flex-fill">
                @if (banner_url('banner_2'))
                    <img src="{{ banner_url('banner_2') }}" alt="Hero Banner 2" class="hero-card-img">
                @else
                    <div class="hero-placeholder">No banner uploaded</div>
                @endif
            </div>

            <!-- Bottom two banners -->
            <div class="row g-3 flex-fill">
                <div class="col-6 d-flex">
                    <div class="hero-card w-100">
                        @if (banner_url('banner_3'))
                            <img src="{{ banner_url('banner_3') }}" alt="Hero Banner 3" class="hero-card-img">
                        @else
                            <div class="hero-placeholder">No banner uploaded</div>
                        @endif
                    </div>
                </div>
                <div class="col-6 d-flex">
                    <div class="hero-card w-100">
                        @if (banner_url('banner_4'))
                            <img src="{{ banner_url('banner_4') }}" alt="Hero Banner 4" class="hero-card-img">
                        @else
                            <div class="hero-placeholder">No banner uploaded</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
    /* Compact hero like reference screenshot */
.hero-compact-row {
    --bs-gutter-x: 0.75rem;
    --bs-gutter-y: 0.75rem;
}

.hero-card {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    background: #0f172a;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.25);
    display: flex;
}

.hero-card-main {
    min-height: 260px; /* main big image height */
}

/* Right-side cards share the row height */
.hero-compact .col-lg-4 {
    min-height: 260px;
}

.hero-compact .col-lg-4 > .hero-card,
.hero-compact .col-lg-4 > .row {
    flex: 1 1 0;
}

.hero-card-img,
.hero-placeholder {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Placeholder for missing banners */
.hero-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 0.9rem;
    background: repeating-linear-gradient(
        135deg,
        #1f2937,
        #1f2937 10px,
        #111827 10px,
        #111827 20px
    );
}

/* Subtle hover */
.hero-card-img {
    transition: transform 0.4s ease;
}

.hero-card:hover .hero-card-img {
    transform: scale(1.03);
}

/* Mobile tweak */
@media (max-width: 991.98px) {
    .hero-card-main,
    .hero-compact .col-lg-4 {
        min-height: 200px;
    }
}

</style>