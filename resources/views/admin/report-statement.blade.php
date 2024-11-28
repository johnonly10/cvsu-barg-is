@extends('layouts.admin')

@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Billing Statement</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Billing Statement</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th class="text-center">Order No</th>
                                <th class="text-center">Name</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Phone</th>
                                <th class="text-center">Reservation Date</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Order Date</th>
                                <th class="text-center">Total Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">{{ $order->id }}</td>
                                <td class="text-center">{{ $order->user->name }}</td>
                                <td class="text-center">{{ $order->user->email }}</td>
                                <td class="text-center">{{ $order->user->phone_number }}</td>
                                <td class="text-center">{{ $order->reservation_date }}</td>
                                <td class="text-center">
                                    @if ($order->status == 'pickedup')
                                        <span class="badge bg-success">Picked Up</span>
                                    @elseif($order->status == 'canceled')
                                        <span class="badge bg-danger">Canceled</span>
                                    @else
                                        <span class="badge bg-warning">Reserved</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $order->created_at->format('F d, Y') }}</td>
                                <td class="text-center">{{ $order->orderItems->count() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4>Order Items</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th class="text-center">Product Name</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderItems as $item)
                                <tr>
                                    <td class="text-center">{{ $item->product->name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">{{ number_format($item->price, 2) }}</td>
                                    <td class="text-center">{{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="total-section">
                    <h4>Summary</h4>
                    <p><strong>Subtotal:</strong> {{ number_format($order->subtotal, 2) }}</p>
                    <p><strong>Total:</strong> {{ number_format($order->total, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 15px;
        text-align: center;
    }

    .table td {
        white-space: nowrap;
    }

    .total-section {
        margin-top: 20px;
    }

    .total-section h4 {
        margin-bottom: 10px;
    }

    .total-section p {
        font-size: 16px;
        margin: 5px 0;
    }
</style>
@endpush
