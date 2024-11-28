@extends('layouts.app')

@section('content')
    <style>
        /* General Styles */
        .pt-90 {
            padding-top: 90px !important;
        }

        .my-account .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 13px;
        }

        .my-account .wg-box {
            background: #fff;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        /* Order Summary Styles */
        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .order-summary .left-section,
        .order-summary .right-section {
            width: 48%;
        }

        .order-summary .order-status {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .order-summary .order-info {
            font-size: 0.9rem;
            color: #555;
        }

        /* Table Styles */
        .table-custom {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .table-custom th,
        .table-custom td {
            padding: 12px;
            border: 1px solid #eaeaea;
            text-align: left;
            vertical-align: middle;
        }

        .table-custom th {
            background-color: #f5f5f5;
            font-weight: 600;
        }

        .product-info {
            display: flex;
            align-items: center;
        }

        .product-info img {
            width: 80px;
            height: 80px;
            margin-right: 15px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
        }

        .product-details {
            font-size: 0.9rem;
            color: #333;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            color: #fff;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        /* Buttons */
        .btn-custom {
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .btn-outline-primary {
            background-color: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }

        .text-end {
            text-align: right;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .order-summary .left-section,
            .order-summary .right-section {
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();

        // Determine the base home route based on user type
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

        // Handle Shop pages
        if ($currentRoute === 'user.orders') {
            $breadcrumbs[] = ['url' => null, 'label' => 'User Orders'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header 
        backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" 
        title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" 
    />

    <main class="container pt-90">
        <section class="my-account">
            <h2 class="page-title">Order Details</h2>

            <div class="wg-box">
                <div class="order-summary">
                    <!-- Left Section -->
                    <div class="left-section">
                        <div class="order-status">
                            @if($order->status == "pickedup")
                                <span class="badge badge-success">Picked Up</span>
                            @elseif($order->status == "canceled")
                                <span class="badge badge-danger">Canceled</span>
                            @else
                                <span class="badge badge-warning">Ordered</span>
                            @endif
                        </div>
                        <div class="order-info">
                            <p><strong>Order No:</strong> {{ $order->id }}</p>
                            <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Reservation Date:</strong> {{ $order->reservation_date ? \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') : 'N/A' }}</p>
                            <p><strong>Picked Up Date:</strong> {{ $order->picked_up_date ? \Carbon\Carbon::parse($order->picked_up_date)->format('M d, Y') : 'N/A' }}</p>
                            <p><strong>Canceled Date:</strong> {{ $order->canceled_date ? \Carbon\Carbon::parse($order->canceled_date)->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="right-section">
                        <div class="order-info">
                            <p><strong>Name:</strong> {{ $order->name }}</p>
                            <p><strong>Phone:</strong> {{ $order->phone_number }}</p>
                            <p><strong>Year Level:</strong> {{ $order->year_level }}</p>
                            <p><strong>Department:</strong> {{ $order->department }}</p>
                            <p><strong>Course:</strong> {{ $order->course }}</p>
                            <p><strong>Time Slot:</strong> {{ $order->time_slot ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a class="btn btn-custom btn-primary" href="{{ route('user.orders') }}">Back to Orders</a>
                </div>
            </div>

            <!-- Ordered Items -->
            <div class="wg-box">
                <h5>Ordered Items</h5>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Variations</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Return Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderItems as $item)
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}" alt="{{ $item->product->name }}">
                                        <div class="product-details">
                                            <p class="py-2">{{ $item->product->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($item->options)
                                        @php $options = json_decode($item->options, true); @endphp
                                        @if (isset($options['variant_attributes']) && !empty($options['variant_attributes']))
                                            @foreach ($options['variant_attributes'] as $attributeName => $value)
                                                <p>{{ $attributeName }}: {{ $value }}</p>
                                            @endforeach
                                        @else
                                            <p>No variations</p>
                                        @endif
                                    @else
                                        <p>No variations</p>
                                    @endif
                                </td>
                                <td>&#8369; {{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>&#8369; {{ number_format($item->price * $item->quantity, 2) }}</td>
                                <td>
                                    @if ($item->rstatus == 0)
                                        <span class="badge badge-success">No Return</span>
                                    @else
                                        <span class="badge badge-warning">Returned</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('shop.product.details', ['product_slug' => $item->product->slug]) }}" 
                                       class="btn btn-custom btn-outline-primary" target="_blank">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div>
                    {{ $orderItems->links('pagination::bootstrap-5') }}
                </div>
            </div>

            <!-- Transactions -->
            <div class="wg-box">
                <h5>Transactions</h5>
                <table class="table-custom">
                    <tr>
                        <th>Subtotal</th>
                        <td>&#8369; {{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>&#8369; {{ number_format($order->total, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($transaction->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif ($transaction->status == 'decline')
                                <span class="badge badge-danger">Declined</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                </table>
                {{-- @if ($order->status == 'reserved')
                    <div class="text-end">
                        <form action="{{ route('user.order.cancel') }}" method="post" style="display: inline;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="order_id" value="{{ $order->id }}" />
                            <button type="button" class="btn btn-custom btn-danger cancel-order">Cancel Order</button>
                        </form>
                    </div>
                @endif --}}
            </div>
        </section>
    </main>
@endsection

@push('scripts')
<script>
    $(function() {
        $('.cancel-order').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to cancel this order?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
