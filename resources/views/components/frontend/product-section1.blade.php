        <section class="product-section">
            <div class="container page-wrapper">

                <!-- Heading & description -->
                @php
                    $productsection1 = section_title($type, 'product section 1');
                @endphp
                <div class="text-center mb-4 mb-md-5">
                    <h2 class="section-title mb-2">{{  $productsection1->section_title }}</h2>
                    <p class="section-subtitle mx-auto text-muted">
                       {{ $productsection1->value }}
                    </p>
                </div>

                <!-- Products grid -->
                <div class="row g-4">
                    <x-frontend.product-card :type="$type" />
                </div>
            </div>
        </section>