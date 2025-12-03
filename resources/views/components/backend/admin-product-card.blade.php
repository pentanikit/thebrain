<div class="col">
    <div class="card card-product-grid">
        <a class="img-wrap" href="#">
            <img
                src="{{ $product->thumbnail
                        ? asset('storage/'.$product->thumbnail)
                        : asset('assets/imgs/theme/placeholder.png') }}"
                alt="{{ $product->name }}">
        </a>

        <div class="info-wrap">
            <a class="title text-truncate" href="#">{{ $product->name }}</a>

            <div class="price mb-2">
                {{ currency($product->offer_price ?? $product->price ?? $product->old_price )}} <span style="text-decoration: line-through; color:grey">{{ currency($product->price) }}</span>
            </div>

            <a class="btn btn-sm font-sm rounded btn-brand"
               href="{{ route('editproduct', $product->id) }}">
                <i class="material-icons md-edit"></i> Edit
            </a>

            <form action=""
                  method="POST"
                  class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Delete this product?')"
                        class="btn btn-sm font-sm btn-light rounded">
                    <i class="material-icons md-delete_forever"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
