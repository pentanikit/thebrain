@extends('backend.layout')

@section('admin')
<main class="main-wrap">
<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Order Details</h4>
        <a href="{{ route('admin.orders') }}" class="btn btn-secondary">
            ← Back to Orders
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">

        {{-- LEFT: Order + Customer --}}
        <div class="col-md-8">

            {{-- Order Info --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Order Information</strong>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="30%">Order Number</th>
                            <td>{{ $order->order_number }}</td>
                        </tr>
                        <tr>
                            <th>Order Date</th>
                            <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Payment Method</th>
                            <td>{{ ucfirst($order->payment_method ?? 'Cash on Delivery') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ 
                                    $order->status === 'completed' ? 'success' :
                                    ($order->status === 'processing' ? 'warning' :
                                    ($order->status === 'cancelled' ? 'danger' : 'secondary'))
                                }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Customer Information</strong>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="30%">Name</th>
                            <td>{{ $order->customer_name }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $order->customer_phone }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ $order->shipping_address }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="card">
                <div class="card-header">
                    <strong>Ordered Items</strong>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>৳ {{ number_format($item->unit_price) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>৳ {{ number_format($item->total_price) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- RIGHT: Summary + Status --}}
        <div class="col-md-4">

            {{-- Order Summary --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Order Summary</strong>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th>Subtotal</th>
                            <td class="text-end">৳ {{ number_format($order->subtotal) }}</td>
                        </tr>
                        <tr>
                            <th>Shipping</th>
                            <td class="text-end">৳ {{ number_format($order->shipping_cost) }}</td>
                        </tr>
                        <tr class="border-top">
                            <th>Total</th>
                            <th class="text-end">৳ {{ number_format($order->total) }}</th>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Update Status --}}
            <div class="card">
                <div class="card-header">
                    <strong>Update Order Status</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
                        @csrf
                        <div class="mb-2">
                            <select name="status" class="form-select">
                                @foreach(['pending','processing','completed','cancelled'] as $status)
                                    <option value="{{ $status }}"
                                        @selected($order->status === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary w-100">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>
</main>
@endsection
