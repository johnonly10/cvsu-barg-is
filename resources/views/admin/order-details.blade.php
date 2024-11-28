@extends('layouts.admin')

@section('content')
    <style>
        .table-transaction>tbody>tr:nth-of-type(odd) {
            --bs-table-accent-bg: #fff !important;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Order Details</h3>
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
                        <div class="text-tiny">Order Details</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <h5>Order Details</h5>
                    </div>
                    <a class="btn btn-sm btn-danger" href="{{ route('admin.orders') }}">Back</a>
                </div>
                <div class="table-responsive">
                    @if (Session::has('status'))
                        <p class="alert alert-success">{{ Session::get('status') }}</p>
                    @endif
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>Order No</th>
                                <td>{{ $order->id ?? 'N/A' }}</td>
                                <th>Phone</th>
                                <td>{{ $order->phone_number ?? 'N/A' }}</td>
                                <th>Year Level</th>
                                <td>{{ $order->year_level ?? 'N/A' }}</td>
                                <th>Department</th>
                                <td>{{ $order->department ?? 'N/A' }}</td>
                                <th>Course</th>
                                <td>{{ $order->course ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Reservation Date</th>
                                <td>{{ $order->reservation_date ?? 'N/A' }}</td>
                                <th>Time</th>
                                <td>{{ $order->time_slot ?? 'N/A' }}</td>
                                <th>Order Date</th>
                                <td>{{ $order->created_at->format('M d, Y') ?? 'N/A' }}</td>
                                <th>Picked Up Date</th>
                                <td>{{ $order->picked_up_date ?? 'N/A' }}</td>
                                <th>Canceled Date</th>
                                <td>{{ $order->canceled_date ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Order Status</th>
                                <td colspan="9">
                                    @if ($order->status == 'pickedup')
                                        <span class="badge bg-success">Picked Up</span>
                                    @elseif($order->status == 'canceled')
                                        <span class="badge bg-danger">Canceled</span>
                                    @else
                                        <span class="badge bg-warning">Reserved</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                        
                    </table>
                </div>
            </div>

            <div class="wg-box mt-5">
                <h5>Ordered Items</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Options</th>
                                {{-- <th class="text-center">Return Status</th> --}}
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orderItems as $item)
                                <tr>
                                    <td>
                                        <div class="image">
                                            <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}"
                                                alt="{{ $item->product->name }}" class="image">
                                        </div>
                                        <div class="name">
                                            <a href="{{ route('shop.product.details', ['product_slug' => $item->product->slug]) }}"
                                                target="_blank" class="body-title-2">
                                                {{ $item->product->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->price }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">{{ $item->product->category->name }}</td>
                                    <td class="text-center">{{ $item->option ?? 'N/A' }}</td>
                                    {{-- <td class="text-center">{{ $item->rstatus == 0 ? 'No' : 'Yes' }}</td> --}}
                                    <td class="text-center">
                                        <div class="list-icon-function view-icon">
                                            <div class="item eye">
                                                <i class="icon-eye"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $orderItems->links('pagination::bootstrap-5') }}
                </div>
            </div>

            <div class="wg-box mt-5">
                <h5>User Information</h5>
                <div class="my-account__address-item col-md-6">
                    <div class="my-account__address-item__detail">
                        <p>{{ $order->name }}</p>
                        <p>{{ $order->year_level }}</p>
                        <p>{{ $order->department }}</p>
                        <p>{{ $order->course }}</p>
                        <p>{{ $order->reservation_date }}</p>
                        <p>{{ $order->time_slot }}</p>
                        <br>
                        <p>Mobile: {{ $order->phone_number }}</p>
                    </div>
                </div>
            </div>

            <div class="wg-box mt-5">
                <h5>Transactions</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-transaction">
                        <tbody>
                            <tr>
                                <th>Price</th>
                                {{-- <td>{{ $item->price }}</td> --}}
                                <th>Subtotal</th>
                                <td>{{ $order->subtotal }}</td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td>{{ $order->total }}</td>
                                <th>Status</th>
                                <td>
                                    @if ($transaction->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif ($transaction->status == 'decline')
                                        <span class="badge bg-danger">Declined</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="wg-box mt-5">
                <h5>Update Order Status</h5>
                <div class="table-responsive">
                    <form action="{{ route('admin.order.status.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                    
                        <input type="hidden" name="order_id" value="{{ $order->id }}" />
                        <div class="row">
                            <div class="col-md-3">
                                <div class="select">
                                    <select name="order_status" id="order_status" onchange="checkStatus()" {{ session('disabled') ? 'disabled' : '' }}>
                                        <option value="reserved" {{ $order->status == 'reserved' ? 'selected' : '' }}>Reserve</option>
                                        <option value="pickedup" {{ $order->status == 'pickedup' ? 'selected' : '' }}>Picked up</option>
                                        <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary tf-button w208" id="submit-button" 
                                    {{ session('disabled') ? 'disabled' : '' }}>
                                    Update Status
                                </button>
                            </div>
                        </div>
                    </form>       
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
          function checkStatus() {
        var status = document.getElementById('order_status').value;
        var submitButton = document.getElementById('submit-button');

        if (status === 'pickedup' || status === 'canceled') {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = false;
        }
    }
    </script>
@endpush

