@extends('layouts.app')

@section('content')
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

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

       
    <div class="container" style="padding-top: 1em;">
        <div class="mb-4 pb-4"></div>
        <h3>Order History</h3>
        <div class="accordion" id="orderHistoryAccordion">
            @forelse($orders as $order)
            @php
                // Calculate the number of days since the order was created
                $daysSinceOrder = \Carbon\Carbon::now()->diffInDays($order->created_at);
                // Calculate the remaining days before the order disappears (30 days limit)
                $daysRemaining = 30 - $daysSinceOrder;
            @endphp

            @if($daysRemaining > 0)
                <div class="alert alert-warning" role="alert">
                    <strong>Note:</strong> The Order #{{ $order->id }} will be removed from your order history in 30 days.
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <strong>Note:</strong> This order will be removed from your order history soon.
                </div>
            @endif
                <div class="accordion-item mb-3 shadow-sm border rounded bg-white text-dark">
                    <h2 class="accordion-header" id="headingOrder{{ $order->id }}">
                        <button
                            class="accordion-button d-flex justify-content-between align-items-center bg-success text-white"
                            type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrder{{ $order->id }}"
                            aria-expanded="true" aria-controls="collapseOrder{{ $order->id }}">
                            <span>
                                <strong>Order #{{ $order->id }}</strong>
                                <small class="text-white">Placed on {{ $order->created_at->format('M d, Y') }}</small>
                            </span>
                            <span class="px-2">
                                @if ($order->status == 'accepted')
                                    <span class="badge bg-success">Accepted</span>
                                @elseif($order->status == 'canceled')
                                    <span class="badge bg-danger">Canceled</span>
                                @endif
                            </span>
                        </button>
                    </h2>
                    <div id="collapseOrder{{ $order->id }}" class="accordion-collapse collapse"
                        aria-labelledby="headingOrder{{ $order->id }}" data-bs-parent="#orderHistoryAccordion">
                        <div class="accordion-body">
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
                                @foreach ($order->orderItems as $item)
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
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p>No order history available.</p>
            @endforelse
        </div>

        <div class="divider"></div>

                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>  
@endsection
