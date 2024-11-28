@extends('layouts.admin')

@section('content')
<style>
    .table-transaction > tbody > tr:nth-of-type(odd) {
        --bs-table-accent-bg: #fff !important;
    }

    .table th,
    .table td {
        vertical-align: middle;
        padding: 10px; /* Reduced padding for a more compact layout */
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .wg-box {
        margin-top: 20px;
        padding: 15px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .text-tiny {
        font-size: 12px;
        color: #999;
    }

    .alert {
        margin-bottom: 20px;
    }

    /* Shorten the width of table header cells */
    .table th {
        width: 150px; /* Set a fixed width for table headers */
        white-space: nowrap; /* Prevent text wrapping */
    }

    .table td {
        white-space: nowrap; /* Prevent text wrapping for table data cells */
    }

    /* Custom styles for select dropdowns */
    .form-select {
        width: 150px; /* Set a fixed width for the dropdowns */
    }

    /* Style for pool quantity input */
    #poolQuantityField {
        display: none; /* Hidden by default */
    }
</style>

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Reservation Events</h3>
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
                    <a href="{{ route('admin.reservation') }}">
                        <div class="text-tiny">Reservation</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Reservation Events</div>
                </li>
            </ul>
        </div>

        <!-- Display success or error messages -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Reservation Details -->
        <div class="wg-box">
            <h5>Reservation Details</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Reservation ID</th>
                            <td>{{ $reservation->id }}</td>
                        </tr>
                        <tr>
                            <th>Rental Name</th>
                            <td>{{ $reservation->rental->name }}</td>
                        </tr>
                        @if ($reservation->rental->name === 'Swimming Pool')
                        <tr id="pool_quantity">
                            <th>Pool Quantity</th>
                            <td>{{ $reservation->pool_quantity }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Reservation Date</th>
                            <td>
                                @if($reservation->rental->name === 'International House II')
                                    @if($reservation->dormitoryRoom)
                                        {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_start_date)->format('Y-m-d') }}
                                        to
                                        {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_end_date)->format('Y-m-d') }}
                                    @else
                                        N/A
                                    @endif
                                @elseif(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory']) && $reservation->dormitoryRoom)
                                    {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->start_date)->format('Y-m-d') }}
                                    to
                                    {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->end_date)->format('Y-m-d') }}
                                @else
                                    {{ $reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('Y-m-d') : 'N/A' }}
                                @endif
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Status</th>
                            <td>{{ ucfirst($reservation->rental->status) }}</td>
                        </tr>
                        @if(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']) && $reservation->rent_status === 'reserved' && $reservation->dormitoryRoom)
                            <tr>
                                <th>Room Number</th>
                                <td>{{ $reservation->dormitoryRoom->room_number }}</td>
                            </tr>
                            <tr>
                                <th>Room Capacity</th>
                                <td>{{ $reservation->dormitoryRoom->room_capacity }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Total Price</th>
                            <td>
                                @if($reservation->rental->name === 'International House II')
                                    @if($reservation->dormitoryRoom)
                                        @php
                                            $start_date = \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_start_date);
                                            $end_date = \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_end_date);
                                            $days = $start_date->diffInDays($end_date);
                                            $calculated_price = $days * $reservation->rental->price; // Assuming rental price is per day
                                        @endphp
                                        &#8369; {{ number_format($calculated_price, 2) }}
                                    @else
                                        <span class="text-danger">No dates selected</span>
                                    @endif
                                @elseif(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory']) && $reservation->dormitoryRoom)
                                    &#8369; {{ number_format($reservation->rental->price, 2) }}
                                @else
                                    &#8369; {{ number_format($reservation->total_price, 2) }}
                                @endif
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Time Slot</th>
                            <td>
                                @if(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']) && $reservation->dormitoryRoom)
                                    <p>Not Applicable</p>
                                @else    
                                    {{ $reservation->time_slot ?? 'N/A' }}
                                @endif    
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Details -->
        <div class="wg-box">
            <h5>User Details</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td>{{ $reservation->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $reservation->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td>{{ $reservation->user->phone_number }}</td>
                        </tr>
                        {{-- Uncomment if needed
                        <tr>
                            <th>Year</th>
                            <td>{{ $reservation->user->year_level }}</td>
                        </tr>
                        <tr>
                            <th>College</th>
                            <td>{{ $reservation->user->department }}</td>
                        </tr>
                        <tr>
                            <th>Course</th>
                            <td>{{ $reservation->user->course }}</td>
                        </tr>
                        --}}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Update Status -->
        <div class="wg-box mt-5">
            <h5>Update Status</h5>
            <div class="table-responsive">
                <form action="{{ route('admin.update-reservation-status', ['reservation_id' => $reservation->id]) }}" method="POST">
                    @csrf
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>Payment Status</th>
                                <td>
                                    <select name="payment_status" class="form-select" required>
                                        <option value="pending" {{ $reservation->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                          
                                        @if(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
                                            <option value="advance/deposit complete" {{ $reservation->payment_status == 'advance/deposit complete' ? 'selected' : '' }}>Advance/Deposit Complete</option>
                                            <option value="1st month complete" {{ $reservation->payment_status == '1st month complete' ? 'selected' : '' }}>1st Month Complete</option>
                                            <option value="2nd month complete" {{ $reservation->payment_status == '2nd month complete' ? 'selected' : '' }}>2nd Month Complete</option>
                                            <option value="3rd month complete" {{ $reservation->payment_status == '3rd month complete' ? 'selected' : '' }}>3rd Month Complete</option>
                                            <option value="4th month complete" {{ $reservation->payment_status == '4th month complete' ? 'selected' : '' }}>4th Month Complete</option>
                                            <option value="5th month complete" {{ $reservation->payment_status == '5th month complete' ? 'selected' : '' }}>5th Month Complete</option>
                                            <option value="6th month complete" {{ $reservation->payment_status == '6th month complete' ? 'selected' : '' }}>6th Month Complete</option>
                                        @endif
        
                                        <option value="full payment complete" {{ $reservation->payment_status == 'full payment complete' ? 'selected' : '' }}>Full Payment Complete</option>
                                        <option value="canceled" {{ $reservation->payment_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Rent Status</th>
                                <td>
                                    <select name="rent_status" class="form-select" required>
                                        <option value="pending" {{ $reservation->rent_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="reserved" {{ $reservation->rent_status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                        <option value="completed" {{ $reservation->rent_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="canceled" {{ $reservation->rent_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="submit" class="tf-button btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to show/hide pool quantity field based on rental name
        const poolQuantityField = document.getElementById('poolQuantityField');
        const rentalName = '{{ $reservation->rental->name }}'; // Get the rental name from the reservation

        // Check if the rental is the Swimming Pool
        if (rentalName === 'Swimming Pool') {
            poolQuantityField.style.display = ''; // Show the pool quantity field
        } else {
            poolQuantityField.style.display = 'none'; // Hide the pool quantity field
        }
    });
</script>
@endsection

@push('styles')
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 10px; /* Reduced padding for a more compact layout */
        text-align: left;
    }

    .table th {
        width: 150px; /* Set a fixed width for table headers */
        white-space: nowrap; /* Prevent text wrapping */
    }

    .table td {
        white-space: nowrap; /* Prevent text wrapping for table data cells */
    }

    /* Shorten dropdown widths */
    .form-select {
        width: 150px; /* Set a fixed width for the dropdowns */
    }
</style>
@endpush
