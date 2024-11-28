@extends('layouts.admin')

@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <!-- Monthly Earned Sales Section -->
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Reports</h3>
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
                        <div class="text-tiny">Reports</div>
                    </li>
                </ul>
            </div>

            

            <!-- Monthly Earned Sales Section with Graph -->
            <div class="wg-box p-4 bg-light shadow-sm rounded-lg mb-4" style="max-width: 100%;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center w-100 btn-lg" type="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-align: left;">
                            <h5 class="mb-0 me-2">Monthly Earned Sales</h5>
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <form action="{{ route('admin.downloadPdf') }}" method="POST">
                            @csrf
                            <input type="hidden" name="monthly_sales_img" id="monthly-sales-img">
                            <input type="hidden" name="weekly_sales_img" id="weekly-sales-img">
                            <input type="hidden" name="daily_sales_img" id="daily-sales-img">
                            <button type="submit" class="btn btn-danger">Download PDF</button>
                        </form>
                    </div>

                    
                    <form action="{{ route('admin.reports') }}" method="GET" class="d-flex align-items-center">
                        <select name="year" class="form-select me-2 custom-width-select">
                            @foreach($yearRange as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-lg">Go</button>
                    </form>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t1"></div>
                                <div class="text-tiny">Total</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${{ $TotalAmount }}</h4>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t2"></div>
                                <div class="text-tiny">Reservation Amount</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${{ $TotalReservedAmount }}</h4>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t2"></div>
                                <div class="text-tiny">Picked Up Amount</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${{ $TotalPickedUpAmount }}</h4>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t2"></div>
                                <div class="text-tiny">Cancelled Orders Amount</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${{ $TotalCanceledAmount }}</h4>
                        </div>
                    </div>
                </div>

                

                <!-- Graph for Monthly Earned Sales -->
                <div id="line-chart-8"></div>
            </div>

            <!-- Earnings Revenue Section -->
            <div class="wg-box p-4 bg-light shadow-sm rounded-lg mb-4" style="max-width: 100%;"> <!-- Full-width container -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center w-100 btn-lg" type="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-align: left;">
                            <h5 class="mb-0 me-2">Weekly Earnings</h5>
                            <i class="fas fa-chart-line"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.reports') }}" method="GET" class="d-flex flex-grow-2 align-items-center">
                        <select name="month" class="form-select me-2 custom-width-select">
                            @foreach($availableMonths as $month)
                                <option value="{{ $month->id }}" {{ $month->id == $selectedMonth->id ? 'selected' : '' }}>
                                    {{ $month->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="year" class="form-select me-2 custom-width-select">
                            @foreach($yearRange as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary w-100">Go</button>
                    </form>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t1"></div>
                                <div class="text-tiny">Total</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${{ $TotalAmountW }}</h4>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t2"></div>
                                <div class="text-tiny">Reservation Amount</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${{ $TotalReservedAmountW }}</h4>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t2"></div>
                                <div class="text-tiny">Picked Up Amount</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${{ $TotalPickedUpAmountW }}</h4>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t2"></div>
                                <div class="text-tiny">Cancelled Orders Amount</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${{ $TotalCanceledAmountW }}</h4>
                        </div>
                    </div>
                </div>

                <!-- Graph for Earnings Revenue -->
                <div id="line-chart-8-weekly"></div>
            </div>

            <!-- Daily Earned Sales Section -->
            <div class="wg-box p-4 bg-light shadow-sm rounded-lg mb-4" style="max-width: 100%;"> <!-- Full-width container -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="dropdown">
                        <!-- Button for Daily Earned Sales -->
                        <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center w-100 btn-lg" type="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-align: left;">
                            <h5 class="mb-0 me-2">Daily Earned Sales</h5>
                            <i class="fas fa-chart-line"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.reports') }}" method="GET" class="d-flex flex-grow-2 align-items-center">
                        <!-- Select week -->
                        <select name="week" class="form-select me-2 custom-width-select-week">
                            @foreach($availableWeeks as $week)
                                <option value="{{ $week->week_number }}" {{ $week->week_number == $selectedWeekId ? 'selected' : '' }}>
                                    Week {{ $week->week_number }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Select month -->
                        <select name="month" class="form-select me-2 custom-width-select">
                            @foreach($availableMonths as $month)
                                <option value="{{ $month->id }}" {{ $month->id == $selectedMonth->id ? 'selected' : '' }}>
                                    {{ $month->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Select year -->
                        <select name="year" class="form-select me-2 custom-width-select">
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
                            <h4>${{ $TotalAmountD }}</h4>
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
                            <h4>${{ $TotalReservedAmountD }}</h4>
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
                            <h4>${{ $TotalPickedUpAmountD }}</h4>
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
                            <h4>${{ $TotalCanceledAmountD }}</h4>
                        </div>
                    </div>
                </div>

                <!-- Graph for Daily Earned Sales -->
                <div id="line-chart-8-daily"></div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .custom-width-select {
        width: 80px; /* Adjust the width as needed */
    }
    .custom-width-select-week {
        width: 80px; /* Adjust the width as needed */
    }
</style>
@endpush

@push('scripts')
<script>
  // Monthly Earned Sales Graph
  (function($) {
        var tfLineChart = (function() {

            var chartBar = function() {
                var options = {
                    series: [{
                        name: 'Total',
                        data: [{{$AmountM}}]
                    }, {      
                        name: 'Reserved',
                        data: [{{$ReservationAmountM}}]
                    }, {
                        name: 'Pickedup',
                        data: [{{$PickedUpAmountM}}]
                    }, {
                        name: 'Canceled',
                        data: [{{$CanceledAmountM}}]
                    }],
                    chart: {
                        type: 'bar',
                        height: 400,
                        width: '100%',
                    },
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    }
                };

                var chart = new ApexCharts(document.querySelector("#line-chart-8"), options);
                chart.render();

                // Capture chart as image
                chart.dataURI().then((imgURI) => {
                    document.getElementById('monthly-sales-img').value = imgURI.imgURI;
                });
            };

            return {
                load: function() {
                    chartBar();
                }
            };
        })();

        jQuery(window).on("load", function() {
            tfLineChart.load();
        });
    })(jQuery);

    // Earnings Revenue Graph
    document.addEventListener('DOMContentLoaded', function() {
        var options = {
            chart: {
                type: 'bar',
                height: 400, 
                width: '100%', 
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
                categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'], // Updated categories
                title: {
                    text: 'Weeks of the Month',
                },
            },
            legend: {
                show: false // Hide legend for cleaner look
            },
            yaxis: {
                title: {
                    show: false
                }
            },
            title: {
                text: 'Weekly Sales Report for {{ $selectedMonth->name }} {{ $selectedYear }}',
                align: 'center'
            }
        };

        var chart = new ApexCharts(document.querySelector("#line-chart-8-weekly"), options); // Chart ID
        chart.render();

        // Capture chart as image
        chart.dataURI().then((imgURI) => {
            document.getElementById('weekly-sales-img').value = imgURI.imgURI; // Store image data in hidden input
        });
    });

    // Daily Earned Sales Graph
    document.addEventListener('DOMContentLoaded', function() {
        var options = {
            chart: {
                type: 'bar',
                height: 400,
                width: '100%', 
                toolbar: {
                    show: false  
                }
            },
            series: [{
                name: 'Total Amount',
                data: [{{ $AmountD }}]
            }, {
                name: 'Reserved Amount',
                data: [{{ $ReservationAmountD }}]
            }, {
                name: 'Picked Up Amount',
                data: [{{ $PickedUpAmountD }}]
            }, {
                name: 'Canceled Amount',
                data: [{{ $CanceledAmountD }}]
            }],
            xaxis: {
                categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                title: {
                    text: 'Day of the Week'
                }
            },
            yaxis: {
                title: {}
            },
            legend: {
                show: false 
            },
            title: {
                text: 'Daily Sales Report for {{ $selectedMonth->name }} {{ $selectedYear }} (Week {{ $selectedWeekId }})',
                align: 'center'
            }
        };

        var chart = new ApexCharts(document.querySelector("#line-chart-8-daily"), options);
        chart.render();

        chart.dataURI().then((imgURI) => {
            document.getElementById('daily-sales-img').value = imgURI.imgURI; 
        });
    });
</script>
@endpush
