                        <div class="col">
                            <div class="card card-product-grid"><a class="img-wrap" href="#"><img
                                        src="{{ asset('storage').'/'. $product->images->path }}" alt="Product"></a>
                                <div class="info-wrap"><a class="title text-truncate" href="#">{{ $product->name }}</a>
                                    <div class="price mb-2">{{ $product->offer_price ?? $product->price ?? $product->old_price }}</div>
                                    <!-- price.//--><a class="btn btn-sm font-sm rounded btn-brand" href="#"><i
                                            class="material-icons md-edit"></i> Edit</a><a
                                        class="btn btn-sm font-sm btn-light rounded" href="#"><i
                                            class="material-icons md-delete_forever"></i> Delete</a>
                                </div>
                            </div>
                            <!-- card-product  end//-->
                            <!-- col.//-->
                        </div>
