                    <!-- Product 1 -->
                    @forelse ($products as $item)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card h-100  d-flex flex-column">
                            @php 
                                    $reg = $item->price;
                                    $offer = $item->offer_price;
                                    $discount = 0;

                                    // Only calculate if regular price is greater than 0
                                    if ($reg > 0 && $offer < $reg) {
                                        $discount = round((($reg - $offer) / $reg) * 100);
                                    }
                                @endphp

                                {{-- Only show the badge if there is a discount greater than 0 --}}
                                @if($discount > 0)
                                    <div class="product-badge">Save: {{ $discount }}%</div>
                                @endif

                               
                                    <!-- Replace with real image -->
                                    <img src="{{ asset('storage').'/'.$item->thumbnail }}"
                                        alt="{{ $item->name }}"  style="object-fit: cover; border-radius: 5%;" >
                               

                                    <h3 class="py-2 product-title text-center">
                                       <a style="color: black; text-decoration:none;" href="{{ route('singleproduct', $item->id) }}"> {{ $item->name }}</a>
                                    </h3>

                               

                                <div class="product-price mb-3 text-center">
                                    <span class="price-current">{{ currency($item->offer_price ?? $item->old_price ?? $item->regular_price) }}</span>
                                    <span class="price-old">{{ currency($item->old_price ?? $item->regular_price) }}</span>
                                </div>

                                <a href="{{ route('singleproduct', $item->id) }}" class="btn btn-product w-100">Buy Now</a>
                            </div>
                        </div>
                    @empty
                        <p>No Products Found</p>
                    @endforelse
