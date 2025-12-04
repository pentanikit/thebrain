        <!-- HERO -->
        <section class="mb-5">
            <div class="row g-4 align-items-stretch">
                <!-- Left big banner -->
                <div class="col-lg-8">
                    <div class="banner-card ratio ratio-16x9">
                        <span class="banner-badge">Latest Design Caps</span>
                        <!-- Replace with main TV banner image -->
                            @if (banner_url('banner_1'))
                                <img src="{{ banner_url('banner_1') }}" alt="Hero Banner 1" class="img-fluid">
                            @else
                                <div class="placeholder-banner text-muted d-flex align-items-center justify-content-center">
                                    No banner uploaded
                                </div>
                            @endif
                    </div>
                </div>

                <!-- Right side banners -->
                <div class="col-lg-4 d-flex flex-column gap-4">
                    <div class="small-banner ratio ratio-16x9">
                        <span class="banner-badge">Live Football in 4K</span>
                            @if (banner_url('banner_2'))
                                <img src="{{ banner_url('banner_2') }}" alt="Hero Banner 2" class="img-fluid">
                            @else
                                <div class="placeholder-banner text-muted d-flex align-items-center justify-content-center">
                                    No banner uploaded
                                </div>
                            @endif
                    </div>

                    <div class="row g-4">
                        <div class="col-6">
                            <div class="small-banner ratio ratio-16x9">
                            @if (banner_url('banner_3'))
                                <img src="{{ banner_url('banner_3') }}" alt="Hero Banner 3" class="img-fluid">
                            @else
                                <div class="placeholder-banner text-muted d-flex align-items-center justify-content-center">
                                    No banner uploaded
                                </div>
                            @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="small-banner ratio ratio-16x9">
                            @if (banner_url('banner_4'))
                                <img src="{{ banner_url('banner_4') }}" alt="Hero Banner 4" class="img-fluid">
                            @else
                                <div class="placeholder-banner text-muted d-flex align-items-center justify-content-center">
                                    No banner uploaded
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>