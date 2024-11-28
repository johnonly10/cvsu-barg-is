
@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <!-- Rentals Reports by Name Header -->
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Rentals Reports by Name</h3>
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
                    <div class="text-tiny">Rentals Reports by Name</div>
                </li>
            </ul>
        </div>

        <!-- Combined Rentals Reports by Name Section -->
        <div class="wg-box p-4 bg-light shadow-sm rounded-lg mb-4" style="max-width: 100%;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center w-100 btn-lg" type="button"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-align: left;">
                        <h5 class="mb-0 me-2">Rentals Reports by Name</h5>
                        <i class="fas fa-chart-line"></i>
                    </button>
                    <!-- Combined Form for PDF Download -->
                    <form id="download-pdf-form" action="{{ route('admin.downloadPdfRentalsName') }}" method="POST">
                        @csrf
                        <input type="hidden" name="monthly_reservations_img" id="monthly-reservations-img">
                        <input type="hidden" name="weekly_reservations_img" id="weekly-reservations-img">
                        <input type="hidden" name="daily_reservations_img" id="daily-reservations-img">
                        
                        <!-- Hidden inputs for summary data -->
                        @foreach($rentalNames as $rental)
                            <input type="hidden" name="reservations_{{ Str::snake($rental->name) }}" value="{{ $reservationsPerRental[$rental->name] ?? 0 }}">
                        @endforeach
                        
                        <button type="submit" class="btn btn-danger mt-2">Download PDF</button>
                    </form>
                </div>

                <form action="{{ route('admin.rentalsReportsName') }}" method="GET" class="d-flex align-items-center">
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
                @foreach($rentalNames as $rental)
                    <div class="col-md-4 mb-3">
                        <div class="card summary-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="dot" style="background-color: {{ $rentalColors[$rental->name] ?? '#000' }};"></div>
                                <div>
                                    <p class="mb-1 text-tiny">{{ $rental->name }}</p>
                                    <h4>{{ number_format($reservationsPerRental[$rental->name] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <!-- Monthly Reservations Chart -->
                <div class="col-md-12 mb-4">
                    <h5>Monthly Reservations Report by Rental Name</h5>
                    <div id="monthly-reservations-chart"></div>
                </div>

                <!-- Weekly Reservations Chart -->
                <div class="col-md-12 mb-4 mt-5">
                    <h5>Weekly Reservations Report by Rental Name</h5>
                    <div id="weekly-reservations-chart"></div>
                </div>

                <!-- Daily Reservations Chart -->
                <div class="col-md-12 mb-4 mt-5">
                    <h5>Daily Reservations Report by Rental Name</h5>
                    <div id="daily-reservations-chart"></div>
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
    #monthly-reservations-chart,
    #weekly-reservations-chart,
    #daily-reservations-chart {
        height: 600px; /* Increased height for higher resolution images */
    }
</style>
@endpush

@push('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Monthly Reservations Reports Graph
        var monthlyReservationsChart = new ApexCharts(document.querySelector("#monthly-reservations-chart"), {
            series: [
                @foreach($rentalNames as $rental)
                {
                    name: '{{ $rental->name }}',
                    data: [{{ implode(',', $monthlyData[$rental->name]) }}]
                },
                @endforeach
            ],
            chart: {
                type: 'bar',
                height: '100%',
                width: '100%',
                id: 'monthly-reservations-chart',
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
                        return val + " Reservations";
                    }
                }
            },
            colors: [
                @foreach($rentalNames as $rental)
                    "{{ $rentalColors[$rental->name] ?? '#000' }}",
                @endforeach
            ]
        });
        monthlyReservationsChart.render();

        // Initialize Weekly Reservations Reports Graph
        var weeklyReservationsChart = new ApexCharts(document.querySelector("#weekly-reservations-chart"), {
            series: [
                @foreach($rentalNames as $rental)
                {
                    name: '{{ $rental->name }}',
                    data: [{{ implode(',', $weeklyData[$rental->name]) }}]
                },
                @endforeach
            ],
            chart: {
                type: 'bar',
                height: '100%',
                width: '100%', 
                id: 'weekly-reservations-chart',
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
                        return val + " Reservations";
                    }
                }
            },
            colors: [
                @foreach($rentalNames as $rental)
                    "{{ $rentalColors[$rental->name] ?? '#000' }}",
                @endforeach
            ]
        });
        weeklyReservationsChart.render();

        // Initialize Daily Reservations Reports Graph
        var dailyReservationsChart = new ApexCharts(document.querySelector("#daily-reservations-chart"), {
            series: [
                @foreach($rentalNames as $rental)
                {
                    name: '{{ $rental->name }}',
                    data: [{{ implode(',', $dailyData[$rental->name]) }}]
                },
                @endforeach
            ],
            chart: {
                type: 'bar',
                height: '100%',
                width: '100%', 
                id: 'daily-reservations-chart',
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
                        return val + " Reservations";
                    }
                }
            },
            colors: [
                @foreach($rentalNames as $rental)
                    "{{ $rentalColors[$rental->name] ?? '#000' }}",
                @endforeach
            ]
        });
        dailyReservationsChart.render();

        // Function to capture charts as images
        function captureCharts() {
            return Promise.all([
                monthlyReservationsChart.dataURI(),
                weeklyReservationsChart.dataURI(),
                dailyReservationsChart.dataURI(),
            ]);
        }

        // Handle PDF download
        document.querySelector('#download-pdf-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Capture chart images
            captureCharts().then(function(chartImages) {
                // Set hidden inputs with base64 chart images
                document.querySelector('#monthly-reservations-img').value = chartImages[0].imgURI;
                document.querySelector('#weekly-reservations-img').value = chartImages[1].imgURI;
                document.querySelector('#daily-reservations-img').value = chartImages[2].imgURI;

                // Submit the form after setting all hidden inputs
                e.target.submit();
            });
        });
    });
</script>
@endpush
