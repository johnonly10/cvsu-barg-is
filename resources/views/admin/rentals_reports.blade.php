@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <!-- Rentals Reports Header -->
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Rentals Reports</h3>
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
                    <div class="text-tiny">Rentals Reports</div>
                </li>
            </ul>
        </div>

        <!-- Combined Rentals Reports Section -->
        <div class="wg-box p-4 bg-light shadow-sm rounded-lg mb-4" style="max-width: 100%;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center w-100 btn-lg" type="button"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-align: left;">
                        <h5 class="mb-0 me-2">Rentals Reports</h5>
                        <i class="fas fa-chart-line"></i>
                    </button>
                    <!-- Combined Form for PDF Download -->
                    <form id="download-pdf-form" action="{{ route('admin.downloadPdfRentals') }}" method="POST">
                        @csrf
                        <input type="hidden" name="monthly_rentals_img" id="monthly-rentals-img">
                        <input type="hidden" name="weekly_rentals_img" id="weekly-rentals-img">
                        <input type="hidden" name="daily_rentals_img" id="daily-rentals-img">
                        
                        <!-- Hidden inputs for summary data -->
                        <input type="hidden" name="total_payment" value="{{ $TotalPaymentAmount }}">
                        <input type="hidden" name="pending_amount" value="{{ $TotalPaymentPendingAmount }}">
                        <input type="hidden" name="completed_amount" value="{{ $TotalPaymentCompletedAmount }}">
                        <input type="hidden" name="canceled_amount" value="{{ $TotalPaymentCanceledAmount }}">
                        
                        <button type="submit" class="btn btn-danger mt-2">Download PDF</button>
                    </form>
                </div>

                <form action="{{ route('admin.rentals_reports') }}" method="GET" class="d-flex align-items-center">
                    <select name="year" class="form-select me-2 custom-width-select">
                        @foreach($yearRange as $year)
                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-lg">Confirm</button>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <!-- Total Payment Amount -->
                <div class="col-md-3 mb-3">
                    <div class="card summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="dot p1"></div>
                            <div>
                                <p class="mb-1 text-tiny">Total Payment Amount</p>
                                <h4>${{ number_format($TotalPaymentAmount, 2) }}</h4>
                            </div>
                        </div>
                    </div> 
                </div>

                <!-- Payment Pending Amount -->
                <div class="col-md-3 mb-3">
                    <div class="card summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="dot p2"></div>
                            <div>
                                <p class="mb-1 text-tiny">Payment Pending Amount</p>
                                <h4>${{ number_format($TotalPaymentPendingAmount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Completed Amount -->
                <div class="col-md-3 mb-3">
                    <div class="card summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="dot p3"></div>
                            <div>
                                <p class="mb-1 text-tiny">Payment Completed Amount</p>
                                <h4>${{ number_format($TotalPaymentCompletedAmount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Canceled Amount -->
                <div class="col-md-3 mb-3">
                    <div class="card summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="dot p4"></div>
                            <div>
                                <p class="mb-1 text-tiny">Payment Canceled Amount</p>
                                <h4>${{ number_format($TotalPaymentCanceledAmount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <!-- Monthly Rentals Chart -->
                <div class="col-md-12 mb-4">
                    <h5>Monthly Rentals Report</h5>
                    <div id="monthly-rentals-chart"></div>
                </div>

                <!-- Weekly Rentals Chart -->
                <div class="col-md-12 mb-4 mt-5">
                    <h5>Weekly Rentals Report</h5>
                    <div id="weekly-rentals-chart"></div>
                </div>

                <!-- Daily Rentals Chart -->
                <div class="col-md-12 mb-4 mt-5">
                    <h5>Daily Rentals Report</h5>
                    <div id="daily-rentals-chart"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .custom-width-select {
        width: 80px; /* Adjust the width as needed */
    }

    /* Custom styles for summary card dots */
    .dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 10px;
    }
    .p1 { background-color: #6f42c1; } /* Total Payment Amount - Purple */
    .p2 { background-color: #fd7e14; } /* Payment Pending Amount - Orange */
    .p3 { background-color: #20c997; } /* Payment Completed Amount - Cyan */
    .p4 { background-color: #e83e8c; } /* Payment Canceled Amount - Pink */

    /* Summary Card Styles */
    .summary-card {
        border: none;
        background: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 8px;
    }
    .summary-card .card-body {
        padding: 20px;
    }
    .text-tiny {
        font-size: 0.8rem;
        color: #6c757d;
    }

    /* Adjustments for larger charts in PDF */
    @media print {
        .chart img {
            max-width: none;
            width: 100%;
            height: auto;
        }
    }

    /* Increase chart size on web page for better PDF quality */
    #monthly-rentals-chart,
    #weekly-rentals-chart,
    #daily-rentals-chart {
        height: 600px; /* Increased height for higher resolution images */
    }
</style>
@endpush

@push('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Monthly Rentals Reports Graph
        var monthlyRentalsChart = new ApexCharts(document.querySelector("#monthly-rentals-chart"), {
            series: [{
                name: 'Total Payment Amount',
                data: [{{ $PaymentAmountM }}]
            }, {
                name: 'Payment Pending Amount',
                data: [{{ $PaymentPendingAmountM }}]
            }, {
                name: 'Payment Completed Amount',
                data: [{{ $PaymentCompletedAmountM }}]
            }, {
                name: 'Payment Canceled Amount',
                data: [{{ $PaymentCanceledAmountM }}]
            }],
            chart: {
                type: 'bar',
                height: '100%', // Responsive to container height
                width: '100%',
                id: 'monthly-rentals-chart',
                toolbar: {
                    show: false,
                },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '40%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                labels: {
                    style: {
                        colors: '#212529',
                    },
                },
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
                    'Oct', 'Nov', 'Dec'
                ],
            },
            yaxis: {
                title: {
                    show:false
                },
                labels: {
                    show: true,
                    style: {
                        colors: '#212529',
                    },
                },
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "$ " + val;
                    }
                }
            },
            colors: ['#6f42c1', '#fd7e14', '#20c997', '#e83e8c'] // Purple, Orange, Cyan, Pink
        });
        monthlyRentalsChart.render();

        // Initialize Weekly Rentals Reports Graph
        var weeklyRentalsChart = new ApexCharts(document.querySelector("#weekly-rentals-chart"), {
            series: [{
                name: 'Total Payment Amount',
                data: [{{ $PaymentAmountW }}]
            }, {
                name: 'Payment Pending Amount',
                data: [{{ $PaymentPendingAmountW }}]
            }, {
                name: 'Payment Completed Amount',
                data: [{{ $PaymentCompletedAmountW }}]
            }, {
                name: 'Payment Canceled Amount',
                data: [{{ $PaymentCanceledAmountW }}]
            }],
            chart: {
                type: 'bar',
                height: '100%', // Responsive to container height
                width: '100%', 
                id: 'weekly-rentals-chart',
                toolbar: {
                    show: false  
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '40%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                labels: {
                    style: {
                        colors: '#212529',
                    },
                },
                categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
                title: {
                    text: 'Weeks of the Month',
                },
            },
            yaxis: {
                title: {
                    show:false
                },
                labels: {
                    show: true,
                    style: {
                        colors: '#212529',
                    },
                },
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "$ " + val;
                    }
                }
            },
            colors: ['#6f42c1', '#fd7e14', '#20c997', '#e83e8c'] // Purple, Orange, Cyan, Pink
        });
        weeklyRentalsChart.render();

        // Initialize Daily Rentals Reports Graph
        var dailyRentalsChart = new ApexCharts(document.querySelector("#daily-rentals-chart"), {
            series: [{
                name: 'Total Payment Amount',
                data: [{{ $AmountD }}]
            }, {
                name: 'Payment Pending Amount',
                data: [{{ $PaymentPendingAmountD }}]
            }, {
                name: 'Payment Completed Amount',
                data: [{{ $PaymentCompletedAmountD }}]
            }, {
                name: 'Payment Canceled Amount',
                data: [{{ $PaymentCanceledAmountD }}]
            }],
            chart: {
                type: 'bar',
                height: '100%', // Responsive to container height
                width: '100%', 
                id: 'daily-rentals-chart',
                toolbar: {
                    show: false  
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '40%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                labels: {
                    style: {
                        colors: '#212529',
                    },
                },
                categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                title: {
                    text: 'Day of the Week'
                }
            },
            yaxis: {
                title: {
                    show:false
                },
                labels: {
                    show: true,
                    style: {
                        colors: '#212529',
                    },
                },
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "$ " + val;
                    }
                }
            },
            colors: ['#6f42c1', '#fd7e14', '#20c997', '#e83e8c'] // Purple, Orange, Cyan, Pink
        });
        dailyRentalsChart.render();

        // Function to capture charts as images
        function captureCharts() {
            return Promise.all([
                monthlyRentalsChart.dataURI(),
                weeklyRentalsChart.dataURI(),
                dailyRentalsChart.dataURI(),
            ]);
        }

        // Handle PDF download
        document.querySelector('#download-pdf-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Capture chart images
            captureCharts().then(function(chartImages) {
                // Set hidden inputs with base64 chart images
                document.querySelector('#monthly-rentals-img').value = chartImages[0].imgURI;
                document.querySelector('#weekly-rentals-img').value = chartImages[1].imgURI;
                document.querySelector('#daily-rentals-img').value = chartImages[2].imgURI;

                // Submit the form after setting all hidden inputs
                e.target.submit();
            });
        });
    });
</script>
@endpush
