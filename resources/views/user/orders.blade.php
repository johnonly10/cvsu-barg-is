@extends('layouts.app')

@section('content')
    <style>
        .table> :not(caption)>tr>th {
            padding: 0.625rem 1.5rem .625rem !important;
            background-color: #b3b892 !important;
        }

        .table>tr>td {
            padding: 0.625rem 1.5rem .625rem !important;
        }

        .table-bordered> :not(caption)>tr>th,
        .table-bordered> :not(caption)>tr>td {
            border-width: 1px 1px;
            border-color: #6a6e51;
        }

        .table> :not(caption)>tr>td {
            padding: .8rem 1rem !important;
        }

        .bg-success {
        background-color: #40c710 !important;            
        }

        .bg-danger {
                background-color: #f44032 !important;
        }

        .bg-warning {
                background-color: #f5d700 !important;
        }

        .accordion-item {
            background-color: #fff;
            border-radius: 5px;
        }

        .accordion-button {
            background-color: #f8f9fa;
        }

        .accordion-body {
            padding: 1rem;
        }

        .order-items .order-item {
            padding: 0.5rem 0;
        }

        .order-items .order-item .text-muted {
            font-size: 0.9rem;
        }

        .btn-outline-primary {
            font-size: 0.875rem;
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

    <main class="container" style="padding-top: 1em;">
        <div class="mb-4 pb-4"></div>

        <section class="my-account container">
            <h2 class="page-title">Orders</h2>

            <div class="row">
                <div class="col-lg-2">
                    @include('user.account__nav')
                </div>

                <div class="col-lg-10">
                <div class="accordion mt-5" id="orderAccordion">
                    @foreach ($orders as $order)
                        <div class="accordion-item mb-3 shadow-sm border rounded">
                            <h2 class="accordion-header" id="headingOrder{{ $order->id }}">
                                <button class="accordion-button d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrder{{ $order->id }}" aria-expanded="true" aria-controls="collapseOrder{{ $order->id }}">
                                    <span>
                                        <strong>Order #{{ $order->id }}</strong>
                                        <small class="text-muted">Placed on {{ $order->created_at->format('M d, Y') }}</small>
                                    </span>
                                    <span>
                                        @if ($order->status == 'pickedup')
                                            <span class="badge bg-success">Picked Up</span>
                                        @elseif($order->status == 'canceled')
                                            <span class="badge bg-danger">Canceled</span>
                                        @else
                                            <span class="badge bg-warning">Ordered</span>
                                        @endif
                                    </span>
                                </button>
                            </h2>
                            <div id="collapseOrder{{ $order->id }}" class="accordion-collapse collapse @if($loop->first) show @endif" aria-labelledby="headingOrder{{ $order->id }}" data-bs-parent="#orderAccordion">
                                <div class="accordion-body">
                                    <!-- Order Summary Section -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <div>
                                            <p><strong>Name: </strong>{{ $order->name }}</p>
                                            <p><strong>Phone: </strong>{{ $order->phone_number }}</p>
                                        </div>
                                        <div>
                                            <p><strong>Order Total: </strong>₱{{ number_format($order->total, 2) }}</p>
                                            <p><strong>Items: </strong>{{ $order->orderItems->count() }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Collapsible Order Items Details -->
                                    <div class="order-items">
                                        @foreach($order->orderItems as $item)
                                            <div class="order-item d-flex justify-content-between py-2 border-bottom">
                                                <div>
                                                    <strong>{{ $item->product->name }}</strong>
                                                    <p class="text-muted">Qty: {{ $item->quantity }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-muted">₱{{ number_format($item->price, 2) }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Order Footer Details -->
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <p class="text-muted"><strong>Reservation Date:</strong> {{ $order->reservation_date }}</p>
                                        <a href="{{ route('user.order.details', ['order_id' => $order->id]) }}" class="btn btn-outline-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>



                    <div class="divider"></div>

                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('styles')
<style>
    .table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 20px;
    text-align: left;
}

.table td {
    white-space: nowrap;
}
</style>
    
@endpush