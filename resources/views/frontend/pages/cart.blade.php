@extends('frontend.layout')

@push('styles')
    <style>
        .content-wrapper{
            margin-top:88px;
            padding-top:1.5rem;
            padding-bottom:3rem;
        }
        @media (max-width:575.98px){
            .content-wrapper{margin-top:76px;}
        }

        /* Cart dropdown (same style as others) */
        .cart-dropdown-menu{
            width:min(340px,100vw - 1.5rem);
            border-radius:16px;
            border:1px solid #e5e7eb;
        }
        .cart-items-list{max-height:260px;overflow-y:auto;}
        .cart-item-thumb{
            width:60px;height:60px;object-fit:contain;border-radius:8px;background:#f9fafb;
        }

        /* Cart page */
        .cart-title{
            font-size:2rem;
            font-weight:700;
        }
        .breadcrumb-link{
            font-size:.9rem;
        }

        .cart-table-card{
            border-radius:18px;
            background:#ffffff;
            border:1px solid #e5e7eb;
            padding:1.4rem 1.6rem;
        }

        .cart-product-img{
            width:80px;height:60px;
            object-fit:contain;
        }

        .cart-product-name{
            font-size:.95rem;
            font-weight:600;
        }

        .qty-box{
            border-radius:999px;
            overflow:hidden;
            border:1px solid #e5e7eb;
            display:inline-flex;
            align-items:center;
        }
        .qty-box button{
            border:0;
            background:#f9fafb;
            width:32px;
            height:32px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:.85rem;
        }
        .qty-box input{
            width:40px;
            border:0;
            text-align:center;
            font-size:.9rem;
        }

        .price-text{
            font-weight:600;
        }

        .cart-summary-card{
            border-radius:18px;
            background:#f3f4f6;
            padding:1.5rem 1.8rem;
            border:1px solid #e5e7eb;
        }

        .btn-checkout{
            background:var(--primary-soft);
            border:none;
            font-weight:600;
            color:#1d4ed8;
        }
        .btn-checkout:hover{
            background:var(--primary);
            color:#fff;
        }

        .delivery-option{
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:14px;
            padding:.65rem .75rem;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.75rem;
            cursor:pointer;
        }
        .delivery-option:hover{
            border-color:#cbd5e1;
        }
        .delivery-option .left{
            display:flex;
            flex-direction:column;
            line-height:1.1;
        }
        .delivery-option .title{
            font-weight:700;
            font-size:.9rem;
        }
        .delivery-option .sub{
            font-size:.8rem;
            color:#6b7280;
        }

        @media (max-width:991.98px){
            .cart-summary-wrapper{
                margin-top:1.5rem;
            }
        }
    </style>
@endpush

@section('pages')
<div class="content-wrapper">
    <div class="container page-wrapper">
        <!-- Title + breadcrumb -->
        <div class="text-center mb-4">
            <h1 class="cart-title mb-1">Cart</h1>
            <div class="breadcrumb-link">
                <a href="{{ url('/') }}" class="text-muted text-decoration-none">Home</a>
                <span class="mx-1">/</span>
                <span class="fw-semibold">Cart</span>
            </div>
        </div>

        @php
            // ===== SAFE site_setting() wrapper =====
            // If helper throws / missing key / null => fallback to 0
            $safe_setting = function ($key, $default = 0) {
                try {
                    $val = site_setting($key);
                    if ($val === null || $val === '') return $default;
                    if (is_numeric($val)) return (int) $val;
                    return $default;
                } catch (\Throwable $e) {
                    return $default;
                }
            };

            $deliveryInside  = site_setting('shipping_charge_inside_dhaka', 0);
            $deliveryOutside = site_setting('shipping_charge_outside_dhaka', 0);

            // Calculate subtotal and total item count
            $subtotal = 0;
            $totalItems = 0;

            foreach ($items as $ci) {
                $unitPrice = $ci->price ?? ($ci->product->sale_price ?? $ci->product->price ?? 0);
                $qty       = $ci->quantity ?? 1;

                $subtotal  += $unitPrice * $qty;
                $totalItems += $qty;
            }

            $totalItems = $totalItems ?: $items->count();

            // defaults
            $defaultDeliveryType = 'inside_dhaka';
            $defaultShipping = $deliveryInside;
            $defaultPayable  = $subtotal + $defaultShipping;
        @endphp

        <div class="row g-4">
            <!-- Cart table -->
            <div class="col-lg-8">
                <div class="cart-table-card">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="small text-muted">
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col" class="text-center">Price</th>
                                    <th scope="col" class="text-center">Quantity</th>
                                    <th scope="col" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $item)
                                @php
                                    $product    = $item->product ?? null;
                                    $unitPrice  = $item->price ?? ($product->sale_price ?? $product->price ?? 0);
                                    $qty        = $item->quantity ?? 1;
                                    $rowTotal   = $unitPrice * $qty;

                                    // Thumbnail fallback
                                    $imageUrl   = $product?->thumbnail
                                                    ? asset('storage/' . $product->thumbnail)
                                                    : 'https://via.placeholder.com/110x70?text=Product';
                                @endphp
                                <tr data-price="{{ $unitPrice }}" data-item-id="{{ $item->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $imageUrl }}"
                                                 class="cart-product-img me-3"
                                                 alt="{{ $product?->name }}" width="60" height="60">
                                            <div>
                                                <div class="cart-product-name">
                                                    {{ $product?->name ?? 'Product' }}
                                                </div>

                                                {{-- Remove link --}}
                                                <form action="{{ route('cart.remove', $item->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="small text-danger text-decoration-none bg-transparent border-0 p-0">
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="price-text">
                                            ৳<span class="unit-price">{{ number_format($unitPrice, 0) }}</span>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="qty-box">
                                            <button class="btn-qty-minus" type="button">-</button>
                                            <input type="text"
                                                   class="qty-input"
                                                   value="{{ $qty }}"
                                                   readonly>
                                            <button class="btn-qty-plus" type="button">+</button>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="price-text">
                                            ৳<span class="row-total">{{ number_format($rowTotal, 0) }}</span>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        Your cart is empty.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="col-lg-4 cart-summary-wrapper">
                <div class="cart-summary-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold">Subtotal:</span>
                        <span class="fw-bold fs-5 text-danger">
                            ৳<span id="cartSubtotal">{{ number_format($subtotal, 0) }}</span>
                        </span>
                    </div>

                    {{-- Delivery area selection --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">Delivery Area</span>
                            <span class="small text-muted">Choose one</span>
                        </div>

                        <label class="delivery-option mb-2">
                            <span class="left">
                                <span class="title">Inside Dhaka</span>
                                <span class="sub">Shipping ৳{{ number_format(site_setting('shipping_charge_inside_dhaka'), 0) }}</span>
                            </span>
                            <input class="form-check-input m-0"
                                   type="radio"
                                   name="delivery_area_choice"
                                   value="inside_dhaka"
                                   checked>
                        </label>

                        <label class="delivery-option">
                            <span class="left">
                                <span class="title">Outside Dhaka</span>
                                <span class="sub">Shipping ৳{{ number_format(site_setting('shipping_charge_outside_dhaka'), 0) }}</span>
                            </span>
                            <input class="form-check-input m-0"
                                   type="radio"
                                   name="delivery_area_choice"
                                   value="outside_dhaka">
                        </label>

                        @if($deliveryInside === 0 && $deliveryOutside === 0)
                            <div class="small text-muted mt-2">
                                * Shipping charge is not set in settings (defaulting to ৳0).
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Shipping:</span>
                        <span class="fw-bold">
                            ৳<span id="cartShipping">{{ number_format(site_setting('shipping_charge_inside_dhaka'), 0) }}</span>
                        </span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold">Payable:</span>
                        <span class="fw-bold fs-5">
                            ৳<span id="cartPayable">{{ number_format($defaultPayable, 0) }}</span>
                        </span>
                    </div>

                    @if($items->count() > 0)
                        {{-- Checkout button opens modal --}}
                        <button class="btn btn-checkout w-100 py-2 mb-2"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#checkoutModal">
                            Proceed To Checkout (<span id="cartItemsCount">{{ $totalItems }}</span>)
                        </button>
                    @else
                        <button class="btn btn-primary w-100 py-2 mb-2" type="button" disabled>
                            Proceed To Checkout (0)
                        </button>
                    @endif

                    <small class="text-muted d-block">
                        Shipping charges will be added based on your selected delivery area.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CHECKOUT MODAL --}}
@if($items->count() > 0)
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('orders.store') }}" method="POST" id="checkoutForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Place Your Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="customer_phone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email (optional)</label>
                        <input type="email" name="customer_email" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Shipping Address</label>
                        <textarea name="shipping_address" rows="2" class="form-control" required></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="shipping_city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Postcode</label>
                            <input type="text" name="shipping_postcode" class="form-control">
                        </div>
                    </div>

                    {{-- Delivery Area (synced with sidebar) --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Delivery Area</label>
                        <select name="delivery_area" id="deliveryAreaSelect" class="form-select" required>
                            <option value="inside_dhaka" selected>Inside Dhaka (৳{{ number_format(site_setting('shipping_charge_inside_dhaka'), 0) }})</option>
                            <option value="outside_dhaka">Outside Dhaka (৳{{ number_format(site_setting('shipping_charge_outside_dhaka'), 0) }})</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cod">Cash on Delivery</option>
                            <option value="bkash">bKash</option>
                            <option value="card">Card Payment</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Order Notes (optional)</label>
                        <textarea name="notes" rows="2" class="form-control"
                                  placeholder="Any special instructions?"></textarea>
                    </div>

                    {{-- Frontend-calculated values (backend must recalc anyway) --}}
                    <input type="hidden" name="frontend_subtotal" id="frontendSubtotalInput" value="{{ $subtotal }}">
                    <input type="hidden" name="shipping_charge" id="shippingChargeInput" value="{{ $defaultShipping }}">
                    <input type="hidden" name="payable_amount" id="payableAmountInput" value="{{ $defaultPayable }}">
                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">
                        Payable: ৳<span id="modalPayable">{{ number_format($defaultPayable, 0) }}</span>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Confirm Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    const SHIPPING_INSIDE = {{ (int) $deliveryInside }};
    const SHIPPING_OUTSIDE = {{ (int) $deliveryOutside }};

    function getSelectedDeliveryValue() {
        const modalSelect = document.getElementById('deliveryAreaSelect');
        if (modalSelect && modalSelect.value) return modalSelect.value;

        const checkedRadio = document.querySelector('input[name="delivery_area_choice"]:checked');
        return checkedRadio ? checkedRadio.value : 'inside_dhaka';
    }

    function getSelectedShipping() {
        const val = getSelectedDeliveryValue();
        return (val === 'outside_dhaka') ? SHIPPING_OUTSIDE : SHIPPING_INSIDE;
    }

    function syncDeliveryUI(value) {
        // radios
        const radio = document.querySelector(`input[name="delivery_area_choice"][value="${value}"]`);
        if (radio) radio.checked = true;

        // modal select
        const modalSelect = document.getElementById('deliveryAreaSelect');
        if (modalSelect) modalSelect.value = value;
    }

    function recalcCart() {
        let subtotal = 0;
        let totalItems = 0;

        document.querySelectorAll('tbody tr[data-price]').forEach(function (row) {
            const unitPrice = parseInt(row.getAttribute('data-price'), 10) || 0;
            const qtyInput = row.querySelector('.qty-input');
            const qty = parseInt(qtyInput.value, 10) || 0;
            const rowTotal = unitPrice * qty;

            const rowTotalEl = row.querySelector('.row-total');
            if (rowTotalEl) rowTotalEl.textContent = rowTotal;

            subtotal += rowTotal;
            totalItems += qty;
        });

        const shipping = getSelectedShipping();
        const payable = subtotal + shipping;

        // Sidebar
        const subtotalEl = document.getElementById('cartSubtotal');
        if (subtotalEl) subtotalEl.textContent = subtotal;

        const shippingEl = document.getElementById('cartShipping');
        if (shippingEl) shippingEl.textContent = shipping;

        const payableEl = document.getElementById('cartPayable');
        if (payableEl) payableEl.textContent = payable;

        const itemsCountEl = document.getElementById('cartItemsCount');
        if (itemsCountEl) itemsCountEl.textContent = totalItems;

        // Modal + hidden inputs
        const modalPayableEl = document.getElementById('modalPayable');
        if (modalPayableEl) modalPayableEl.textContent = payable;

        const subtotalInput = document.getElementById('frontendSubtotalInput');
        if (subtotalInput) subtotalInput.value = subtotal;

        const shipInput = document.getElementById('shippingChargeInput');
        if (shipInput) shipInput.value = shipping;

        const payableInput = document.getElementById('payableAmountInput');
        if (payableInput) payableInput.value = payable;
    }

    // Quantity buttons
    document.querySelectorAll('.btn-qty-minus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = this.parentElement.querySelector('.qty-input');
            let value = parseInt(input.value, 10) || 1;
            if (value > 1) {
                input.value = value - 1;
                recalcCart();
            }
        });
    });

    document.querySelectorAll('.btn-qty-plus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = this.parentElement.querySelector('.qty-input');
            let value = parseInt(input.value, 10) || 1;
            input.value = value + 1;
            recalcCart();
        });
    });

    // Sidebar delivery radios
    document.querySelectorAll('input[name="delivery_area_choice"]').forEach((r) => {
        r.addEventListener('change', () => {
            syncDeliveryUI(r.value);
            recalcCart();
        });
    });

    // Modal delivery select
    const deliveryAreaSelect = document.getElementById('deliveryAreaSelect');
    if (deliveryAreaSelect) {
        deliveryAreaSelect.addEventListener('change', function () {
            syncDeliveryUI(this.value);
            recalcCart();
        });
    }

    // Modal open sync
    const checkoutModal = document.getElementById('checkoutModal');
    if (checkoutModal) {
        checkoutModal.addEventListener('shown.bs.modal', function () {
            const val = getSelectedDeliveryValue();
            syncDeliveryUI(val);
            recalcCart();
        });
    }

    // Initial calc (adds default shipping)
    recalcCart();
</script>
@endpush
