@extends('layouts.admin')
@section('content')

<main class="container">
    <div class="main-content-inner">
        <div class="main-content-wrap">

            <div class="container my-4">
                <div class="row row-cols-1 row-cols-md-3 g-4">

                    <!-- Total Payment Amount -->
                    <div class="col">
                        <div class="card p-4 text-center shadow-sm border-0">
                            <div class="image ic-bg me-3">
                                <i class="icon-shopping-bag"></i>
                            </div>
                            <div>
                                <div class="body-text mb-2">Total Reservation</div>
                                <h4>{{$dashboardDatas[0]->Total}}</h4>
                            </div>  
                        </div>
                    </div>

                    <!-- Total Amount of Reserve Items -->
                    <div class="col">
                        <div class="card p-4 text-center shadow-sm border-0">
                            <div class="image ic-bg me-3">
                                <i class="icon-dollar-sign"></i>
                            </div>
                            <div>
                                <div class="body-text mb-2">Total Amount of Reserve Items</div>
                                <h4>{{$dashboardDatas[0]->TotalReservedAmount}}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Total Claimed Items -->
                    <div class="col">
                        <div class="card p-4 text-center shadow-sm border-0">
                            <div class="image ic-bg me-3">
                                <i class="icon-shopping-bag"></i>
                            </div>
                            <div>
                                <div class="body-text mb-2">Total Claimed Items</div>
                                <h4>{{$dashboardDatas[0]->TotalPickedUp}}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Total Amount of Claimed Items -->
                    <div class="col">
                        <div class="card p-4 text-center shadow-sm border-0">
                            <div class="image ic-bg me-3">
                                <i class="icon-dollar-sign"></i>
                            </div>
                            <div>
                                <div class="body-text mb-2">Total Amount of Claimed Items</div>
                                <h4>{{$dashboardDatas[0]->TotalPickedUpAmount}}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Total Canceled Orders -->
                    <div class="col">
                        <div class="card p-4 text-center shadow-sm border-0">
                            <div class="image ic-bg me-3">
                                <i class="icon-shopping-bag"></i>
                            </div>
                            <div>
                                <div class="body-text mb-2">Total Canceled Orders</div>
                                <h4>{{$dashboardDatas[0]->TotalCanceled}}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Total Amount of Cancelled Orders -->
                    <div class="col">
                        <div class="card p-4 text-center shadow-sm border-0">
                            <div class="image ic-bg me-3">
                                <i class="icon-dollar-sign"></i>
                            </div>
                            <div>
                                <div class="body-text mb-2">Total Amount of Cancelled Orders</div>
                                <h4>{{$dashboardDatas[0]->TotalCanceledAmount}}</h4>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="main-content-wrap">
                    <div class="tf-section-2 mb-30">
                        <div class="flex gap20 flex-wrap-mobile">
    
                            <div class="wg-box p-4 bg-light shadow-sm rounded-lg w-100" style="max-width: 100%;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-align: left;">
                                <h5 class="mb-0">Earnings Revenue (Weekly)</h5>
                                <i class="fas fa-chart-line"></i>
                            </button>
                
                            <ul class="dropdown-menu dropdown-menu-end w-100 shadow-sm">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center p-3" href="{{ route('admin.index') }}">
                                        <i class="fas fa-calendar-month me-2"></i> Monthly Earnings Revenue
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center p-3" href="{{ route('admin.index-daily') }}">
                                        <i class="fas fa-calendar-week me-2"></i> Daily Earnings Revenue
                                    </a>
                                </li>
                            </ul>
                        </div>
                
                        <form action="{{ route('admin.index-weekly') }}" method="GET" class="d-flex flex-grow-2 align-items-center">
                            <!-- Select month -->
                            <select name="month" class="form-select me-2 w-100">
                                @foreach($availableMonths as $month)
                                    <option value="{{ $month->id }}" {{ $month->id == $selectedMonth->id ? 'selected' : '' }}>
                                        {{ $month->name }}
                                    </option>
                                @endforeach
                            </select>
                
                            <!-- Select year -->
                            <select name="year" class="form-select me-2 w-100">
                                @foreach($yearRange as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Submit button -->
                            <button type="submit" class="btn btn-primary w-100">Go</button>
                        </form>
                    </div>

                    <div class="row mb-4">
                        <!-- Total Amount -->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <div class="block-legend">
                                    <div class="dot t1"></div>
                                    <div class="text-tiny">Total</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <h4>${{$TotalAmountW}}</h4>
                            </div>
                        </div>
                
                        <!-- Reservation Amount -->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <div class="block-legend">
                                    <div class="dot t2"></div>
                                    <div class="text-tiny">Reservation Amount</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <h4>${{$TotalReservedAmountW}}</h4>
                            </div>
                        </div>

                        <!-- Picked Up (Received) Amount -->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <div class="block-legend">
                                    <div class="dot t2"></div>
                                    <div class="text-tiny">Received Amount</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <h4>${{$TotalPickedUpAmountW}}</h4>
                            </div>
                        </div>

                        <!-- Cancelled Orders Amount -->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <div class="block-legend">
                                    <div class="dot t2"></div>
                                    <div class="text-tiny">Cancelled Orders Amount</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <h4>${{$TotalCanceledAmountW}}</h4>
                            </div>
                        </div>
                    </div>
                
                    <!-- Line Chart Placeholder -->
                    <div id="line-chart-8"></div>
                </div>
                
            </div>

                                <!-- Stock Status Table Container -->
                                <div class="container p-4" style="background-color: #f8f9fc; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        
                                    </div>
                                    <div style="overflow-x: auto;">
                                        <!-- Updated Stock Status Table -->
                                        <table class="table table-bordered table-hover" style="table-layout: auto;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-center">Product ID</th>
                                                    <th class="text-center">Name</th>
                                                    {{-- <th class="text-center">Category</th>
                                                    <th class="text-center">Price</th> --}}
                                                    <th class="text-center">Stock Status</th>
                                                    <th class="text-center">Current Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($products as $product)
                                                    @php
                                                        $currentStock = $product->attributeValues->isNotEmpty() 
                                                            ? $product->attributeValues->sum('quantity') 
                                                            : $product->current_stock;
                                                    @endphp
                                            
                                                    @if($currentStock == 0) <!-- Check if the stock is 0 -->
                                            <tr>
                                                <td class="text-center">{{ $product->id }}</td>
                                                <td class="text-center">{{ $product->name }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                </td>
                                                <td class="text-center">{{ $currentStock }}</td>
                                            </tr>
                                        @elseif($currentStock <= $product->reorder_quantity) <!-- Display 'Reorder' and 'Low Stock' items -->
                                            <tr>
                                                <td class="text-center">{{ $product->id }}</td>
                                                <td class="text-center">{{ $product->name }}</td>
                                                <td class="text-center">
                                                    @if($currentStock <= $product->outofstock_quantity)
                                                        <span class="badge bg-danger">Low Stock</span>
                                                    @else
                                                        <span class="badge bg-warning">Reorder Level</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $currentStock }}</td>
                                            </tr>
                                        @endif
                                                @endforeach
                                            </tbody>
                                            
                                        </table>
                                        <!-- End of Updated Stock Status Table -->
                                    </div>
                                </div>
                        
            
                        </div>
                    </div>


            <div class="tf-section mb-30">
                <div class="wg-box">
                    <div class="flex items-center justify-between">
                        <h5>Recent orders</h5>
                        <div class="dropdown default">
                            <a class="btn btn-secondary dropdown-toggle" href="{{ route('admin.orders') }}">
                                <span class="view-all">View all</span>
                            </a>
                        </div>
                    </div>
                    <div class="wg-table table-all-user">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" style="table-layout: auto;">
                                <thead>
                                    <tr>
                                        <th>OrderNo</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Phone</th>
                                        <th class="text-center">Year Level</th>
                                        <th class="text-center">Department</th>
                                        <th class="text-center">Course</th>
                                        <th class="text-center">Reservation Date</th>
                                        <th class="text-center">Time Slot</th>
                                        <th class="text-center">Subtotal</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Order Date</th>
                                        <th class="text-center">Total Items</th>
                                        <th class="text-center">Picked up on</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                    <tr>
                                        <td class="text-center">{{$order->id}}</td>
                                        <td class="text-center">{{$order->name}}</td>
                                        <td class="text-center">{{$order->phone}}</td>
                                        <td class="text-center">{{$order->year_level}}</td>
                                        <td class="text-center">{{$order->department}}</td>
                                        <td class="text-center">{{$order->course}}</td>
                                        <td class="text-center">{{$order->reservation_date}}</td>
                                        <td class="text-center">{{$order->time_slot}}</td>
                                        <td class="text-center">{{$order->subtotal}}</td>
                                        <td class="text-center">{{$order->total}}</td>
                                        <td class="text-center">{{$order->status}}</td>
                                        <td class="text-center">{{$order->created_at}}</td>
                                        <td class="text-center">{{$order->total_items}}</td>
                                        <td class="text-center">{{$order->picked_up_on}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
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

    .row-container {
        display: flex;
        flex-direction: row;
        gap: 20px;
        flex-wrap: wrap;
    }

    .wg-chart-default {
        flex: 1;
        min-width: 200px;
    }

    .container {
        max-width: 100%;
    }

    .table thead {
        background-color: #f1f3f5;
    }

    .table tbody tr:hover {
        background-color: #f9fafb;
    }

    .badge {
        font-size: 0.9rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var options = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            series: [{
                name: 'Total Amount',
                data: [{{ $AmountW }}]
            }, {
                name: 'Reserved Amount',
                data: [{{ $ReservationAmountW }}]
            }, {
                name: 'Picked Up Amount',
                data: [{{ $PickedUpAmountW }}]
            }, {
                name: 'Canceled Amount',
                data: [{{ $CanceledAmountW }}]
            }],
            xaxis: {
                categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
                title: {
                    text: 'Weeks'
                }
            },
            yaxis: {
                title: {
                }
            },
            title: {
                text: 'Weekly Sales Report for {{ $selectedMonth->name }} {{ $selectedYear }}',
                align: 'center'
            }
        };

        var chart = new ApexCharts(document.querySelector("#line-chart-8"), options);
        chart.render();
    });
</script>
@endpush
