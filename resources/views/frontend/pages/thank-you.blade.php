@extends('frontend.layout')

@section('pages')
<div class="content-wrapper">
    <div class="container page-wrapper py-5">

        <div class="text-center mb-4">
            <h1 class="cart-title mb-2">Thank You for Your Order!</h1>
            <p class="text-muted mb-0">
                Your order has been placed successfully. A confirmation message will be sent to you soon.
            </p>
        </div>

        <!-- Order summary card -->
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">Order #{{ $order->order_number }}</h5>
                                <small class="text-muted">
                                    Placed on {{ $order->created_at->format('d M Y, h:i A') }}
                                </small>
                            </div>
                            <span class="badge bg-success text-uppercase">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>

                        <hr>

                        <!-- Customer & Shipping -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Customer Details</h6>
                                <p class="mb-1">{{ $order->customer_name }}</p>
                                <p class="mb-1">Phone: {{ $order->customer_phone }}</p>
                                @if($order->customer_email)
                                    <p class="mb-0">Email: {{ $order->customer_email }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Shipping Address</h6>
                                <p class="mb-1">{{ $order->shipping_address }}</p>
                                <p class="mb-0">
                                    {{ $order->shipping_city }} {{ $order->shipping_postcode }}
                                </p>
                            </div>
                        </div>

                        <!-- Items table -->
                        <h6 class="fw-semibold mb-2">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table align-middle mb-3">
                                <thead class="small text-muted">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $product = $item->product;
                                                    $imageUrl = $product && $product->thumbnail
                                                        ? asset('storage/' . $product->thumbnail)
                                                        : 'https://via.placeholder.com/70x50?text=Product';
                                                @endphp
                                                <img src="{{ $imageUrl }}"
                                                     alt="{{ $item->product_name }}"
                                                     class="me-3 rounded"
                                                     style="width:70px;height:50px;object-fit:cover;">
                                                <div>
                                                    <div class="fw-semibold">
                                                        {{ $item->product_name }}
                                                    </div>
                                                    @if($item->product_sku)
                                                        <small class="text-muted">
                                                            SKU: {{ $item->product_sku }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="text-center">
                                            ৳{{ number_format($item->unit_price, 0) }}
                                        </td>
                                        <td class="text-end">
                                            ৳{{ number_format($item->total_price, 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="d-flex justify-content-end">
                            <div style="min-width:260px;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Subtotal</span>
                                    <span>৳{{ number_format($order->subtotal, 0) }}</span>
                                </div>
                                @if($order->discount > 0)
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Discount</span>
                                        <span>- ৳{{ number_format($order->discount, 0) }}</span>
                                    </div>
                                @endif
                                @if($order->shipping_cost > 0)
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Shipping</span>
                                        <span>৳{{ number_format($order->shipping_cost, 0) }}</span>
                                    </div>
                                @endif
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fw-semibold fs-5">
                                    <span>Total</span>
                                    <span class="text-danger">
                                        ৳{{ number_format($order->total, 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer message -->
                        <div class="mt-4 text-center">
                            <p class="mb-1">
                                Payment Method: <strong>{{ strtoupper($order->payment_method) }}</strong>
                            </p>
                            <p class="text-muted small mb-0">
                                If you have any questions about your order, please contact our support team.
                            </p>
                        </div>

                    </div>
                </div>

                <div class="text-center">
                    <a href="{{ url('/') }}" class="btn btn-primary px-4">
                        Continue Shopping
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
