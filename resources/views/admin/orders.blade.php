@extends('layouts.admin')
@section('content')
    <style>
        /* Enhanced Table Styles */
        .table-container {
            overflow-x: auto;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            margin-bottom: 0;
        }

        .table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Enhanced Badge Styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }



        /* Name Column Enhancement */
        .name-cell {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .name-text {
            font-weight: 500;
            color: #2c3e50;
        }

        /* Enhanced Tooltip */
        .custom-tooltip {
            position: absolute;
            background: #333;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            max-width: 200px;
            white-space: normal;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .table td {
                min-width: 120px;
            }

            .table th,
            .table td {
                padding: 12px 8px;
            }

            .status-badge {
                padding: 4px 8px;
            }
        }

        /* Loading State */
        .table-loading {
            position: relative;
            min-height: 200px;
        }

        .table-loading::after {
            content: "Loading...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1rem;
            color: #666;
        }

        /* Empty State */
        .table-empty {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-button {
            padding: 6px;
            border-radius: 4px;
            background: none;
            border: 1px solid #dee2e6;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-button:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }

        /* Filter Section */
        .filter-section {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            min-width: 150px;
            background: #fff;
        }
    </style>


    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Orders</h3>
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
                        <div class="text-tiny">Orders</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">

                    <div class="filter-dropdowns flex items-center gap10">

                        <!-- Time Slot Dropdown -->
                        <select name="time_slot" id="time_slot" class="">
                            <option value="">Select Time Slot</option>
                            <option value="08:00 am - 09:00 am"
                                {{ request('time_slot') == '08:00 am - 09:00 am' ? 'selected' : '' }}>08:00 am - 09:00 am
                            </option>
                            <option value="10:00 am - 11:00 am"
                                {{ request('time_slot') == '10:00 am - 11:00 am' ? 'selected' : '' }}>10:00 am - 11:00 am
                            </option>
                            <option value="01:00 pm - 02:00 pm"
                                {{ request('time_slot') == '01:00 pm - 02:00 pm' ? 'selected' : '' }}>01:00 pm - 02:00 pm
                            </option>
                            <option value="03:00 pm - 04:00 pm"
                                {{ request('time_slot') == '03:00 pm - 04:00 pm' ? 'selected' : '' }}>03:00 pm - 04:00 pm
                            </option>
                        </select>

                        <!-- Status Dropdown -->
                        <select name="status" id="status" class="">
                            <option value="">Select Status</option>
                            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved
                            </option>
                            <option value="pickedup" {{ request('status') == 'pickedup' ? 'selected' : '' }}>Picked Up
                            </option>
                            <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled
                            </option>
                        </select>

                    </div>
                </div>



                <div class="wg-table table-all-user">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="table-layout: auto;">
                            <thead>
                                <tr>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Reservation Date</th>
                                    <th class="text-center">Total Items</th>
                                    <th class="text-center">Total Price</th>
                                    <th class="text-center">Order Date</th>
                                    <!-- <th class="text-center">Total Items</th>  -->
                                    <th class="text-center">Picked up on</th>
                                    <!-- <th class="text-center">Status</th> -->
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>

                                        <td class="text-start">
                                            <div class="name">
                                                {{ $order->name }}
                                                <div class="status-badge-container">
                                                    <span
                                                        class="badge status-badge 
                                                    {{ $order->status == 'pickedup' ? 'bg-success' : ($order->status == 'canceled' ? 'bg-danger' : 'bg-warning') }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>


                                        <!-- Reservation Date with Tooltip -->
                                        <td class="text-center reservation-date" data-time-slot="{{ $order->time_slot }}">
                                            <span class="reservation-date">
                                                {{ \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') }}
                                            </span>
                                        </td>


                                        <td class="text-center">
                                            {{ $order->orderItems->count() }}
                                            {{ $order->orderItems->count() == 1 ? 'item' : 'items' }}
                                        </td>
                                        <td class="text-center">&#8369;{{ $order->total }}</td>

                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        {{-- <td class="text-center"></td> --}}
                                        <td class="text-center">{{ $order->picked_up_date }}</td>
                                        <!-- <td class="text-center">{{ $order->status }}</td> -->
                                        <td class="text-center">
                                            <a href="{{ route('admin.order.details', ['order_id' => $order->id]) }}">
                                                <i class="icon-eye" title="View Details"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Tooltip for Reservation Date (Optional)
            const tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');
            $('.reservation-date').hover(function() {
                const timeSlot = $(this).data('time-slot');
                tooltip.text(timeSlot).fadeIn('fast');
            }, function() {
                tooltip.hide();
            }).mousemove(function(e) {
                tooltip.css({
                    top: e.pageY + 10 + 'px',
                    left: e.pageX + 10 + 'px'
                });
            });

            $(document).ready(function() {
                // Trigger filtering on dropdown changes
                $('#status, #time_slot').on('change', function() {
                    filterOrders();
                });

                function filterOrders() {
                    let status = $('#status').val(); // Get selected status value
                    let timeSlot = $('#time_slot').val(); // Get selected time slot value

                    // AJAX request to filter orders
                    $.ajax({
                        url: "{{ route('admin.orders.filter') }}", // Your route for filtering orders
                        type: "GET",
                        data: {
                            status: status, // Send status as part of data
                            time_slot: timeSlot // Send time slot as part of data
                        },
                        success: function(data) {
                            updateTable(data, 'No orders found with the selected filters.');
                        },
                        error: function() {
                            alert('An error occurred while filtering orders.');
                        }
                    });
                }

                function updateTable(data, noResultsMessage) {
                    let tableBody = $('tbody');
                    tableBody.empty(); // Clear existing table rows

                    if (data.length > 0) {
                        data.forEach(function(order) {
                            tableBody.append(` 
                              @foreach ($orders as $order)
                     <tr>

                                        <td class="text-start">
                                            <div class="name">
                                                {{ $order->name }}
                                                <div class="status-badge-container">
                                                    <span
                                                        class="badge status-badge 
                                                    {{ $order->status == 'pickedup' ? 'bg-success' : ($order->status == 'canceled' ? 'bg-danger' : 'bg-warning') }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>


                                        <!-- Reservation Date with Tooltip -->
                                        <td class="text-center reservation-date" data-time-slot="{{ $order->time_slot }}">
                                            <span class="reservation-date">
                                                {{ \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') }}
                                            </span>
                                        </td>


                                        <td class="text-center">
                                            {{ $order->orderItems->count() }}
                                            {{ $order->orderItems->count() == 1 ? 'item' : 'items' }}
                                        </td>
                                        <td class="text-center">&#8369;{{ $order->total }}</td>

                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        {{-- <td class="text-center"></td> --}}
                                        <td class="text-center">{{ $order->picked_up_date }}</td>
                                        <!-- <td class="text-center">{{ $order->status }}</td> -->
                                        <td class="text-center">
                                            <a href="{{ route('admin.order.details', ['order_id' => $order->id]) }}">
                                                <i class="icon-eye" title="View Details"></i>
                                            </a>
                                        </td>
                                    </tr>
                    @endforeach
                `);
                        });
                    } else {
                        tableBody.append(`
                <tr>
                    <td colspan="8" class="text-center">${noResultsMessage}</td>
                </tr>
            `);
                    }
                }

                // Helper function to capitalize status
                function capitalizeStatus(status) {
                    return status.charAt(0).toUpperCase() + status.slice(1);
                }

                // Helper function to format date
                function formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('en-US');
                }
            });
        });
    </script>
@endpush