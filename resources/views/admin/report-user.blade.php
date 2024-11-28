@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Reports</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a></li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">User reports</div></li>
            </ul>
        </div>

        <div class="wg-box p-4 bg-light shadow-sm rounded-lg">
            <!-- Updated layout with Flexbox -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Monthly Registered Accounts for {{ $selectedYear }}</h5>
                
                <div class="d-flex gap-2">
                    <!-- Form for selecting month and year -->
                    <form action="{{ route('admin.report-user') }}" method="GET" class="d-flex align-items-center gap-2">
                        <select name="month" class="form-select" style="width: 140px;">
                            @foreach($availableMonths as $month)
                                <option value="{{ $month->id }}" {{ $month->id == $selectedMonth ? 'selected' : '' }}>
                                    {{ $month->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="year" class="form-select" style="width: 100px;">
                            @foreach($yearRange as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary" style="width: 60px;">Go</button>
                    </form>

                    <!-- Form for downloading PDF -->
                    <form id="pdfForm" action="{{ route('admin.report-user.pdf') }}" method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <input type="hidden" name="monthlyChartImage">
                        <input type="hidden" name="weeklyChartImage">
                        <input type="hidden" name="dailyChartImage">
                        <button id="pdfDownloadButton" type="submit" class="btn btn-danger">Download PDF</button>
                    </form>
                </div>
            </div>

            <!-- Monthly Chart -->
            <div class="card-body">
                <div id="user-registration-chart-monthly"></div>
            </div>
        </div>

        <!-- Weekly Chart -->
        <div class="wg-box p-4 bg-light shadow-sm rounded-lg mt-4">
            <div class="card-body">
                <h5>Weekly Registered Users for {{ $availableMonths->firstWhere('id', $selectedMonth)->name }} {{ $selectedYear }}</h5>
                <div id="user-registration-chart-weekly"></div>
            </div>
        </div>

        <!-- Daily Chart -->
        <div class="wg-box p-4 bg-light shadow-sm rounded-lg mt-4">
            <div class="card-body">
                <h5>Daily Registered Users for {{ $availableMonths->firstWhere('id', $selectedMonth)->name }} {{ $selectedYear }}</h5>
                <div id="user-registration-chart-daily"></div>
            </div>
        </div>

        <!-- Recently Registered Users -->
        <div class="mt-4">
            <h5>Recently Registered Users</h5>
            <ul class="list-group">
                @foreach ($recentUsers as $user)
                    <li class="list-group-item fs-5">
                        {{ $user->name }} - Registered on {{ $user->created_at->format('M d, Y') }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Chart
    var monthlyChart = new ApexCharts(document.querySelector("#user-registration-chart-monthly"), {
        series: [{
            name: 'Users Registered',
            data: [{{ $userRegistrationsByMonth }}]
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false // Disable the toolbar
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        },
        yaxis: {
            title: {
                show: false
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " Users";
                }
            }
        }
    });

    monthlyChart.render();

    // Weekly Chart
    var weeklyChart = new ApexCharts(document.querySelector("#user-registration-chart-weekly"), {
        series: [{
            name: 'Users Registered',
            data: [{{ $weeklyChartData }}]
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false // Disable the toolbar
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        xaxis: {
            categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            title: {
                text: 'Weeks of the Month'
            }
        },
        yaxis: {
            title: {
                show: false
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " Users";
                }
            }
        }
    });

    weeklyChart.render();

    // Daily Chart
    var dailyChart = new ApexCharts(document.querySelector("#user-registration-chart-daily"), {
        series: [{
            name: 'Users Registered',
            data: [{{ $dailyChartData }}]
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false // Disable the toolbar
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        xaxis: {
            categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            title: {
                text: 'Days of the Week'
            }
        },
        yaxis: {
            title: {
                show: false
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " Users";
                }
            }
        }
    });

    dailyChart.render();

    // Capture Charts as Base64 for PDF download
    document.querySelector('#pdfDownloadButton').addEventListener('click', function(e) {
        e.preventDefault();

        // Capture chart images
        Promise.all([
            monthlyChart.dataURI(),
            weeklyChart.dataURI(),
            dailyChart.dataURI(),
        ]).then(function(chartImages) {
            // Set hidden inputs with base64 chart images
            document.querySelector('input[name="monthlyChartImage"]').value = chartImages[0].imgURI;
            document.querySelector('input[name="weeklyChartImage"]').value = chartImages[1].imgURI;
            document.querySelector('input[name="dailyChartImage"]').value = chartImages[2].imgURI;

            // Submit the form
            document.querySelector('#pdfForm').submit();
        });
    });
});
</script>
@endpush
