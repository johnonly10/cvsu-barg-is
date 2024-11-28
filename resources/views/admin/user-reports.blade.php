@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <!-- Header with Breadcrumbs -->
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Reports</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a></li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">User reports</div></li>
            </ul>
        </div>

        <!-- Main Content Card -->
        <div class="wg-box p-4 bg-light shadow-sm rounded-lg">
            <!-- Header Section with Controls -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">User Report</h5>
                
                <div class="d-flex gap-2">
                    <!-- Date Range Form -->
                    <form action="{{ route('admin.user-reports.generate') }}" method="POST" id="report-form" 
                          class="d-flex align-items-center gap-2">
                        @csrf
                        <div class="d-flex gap-2">
                            <div>
                                <input 
                                    type="date" 
                                    name="start_date" 
                                    id="start_date"
                                    class="form-select"
                                    style="width: 140px;"
                                    value="{{ old('start_date', isset($startDate) ? $startDate->toDateString() : '') }}" 
                                    required
                                >
                            </div>
                            <div>
                                <input 
                                    type="date" 
                                    name="end_date" 
                                    id="end_date" 
                                    class="form-select"
                                    style="width: 140px;"
                                    value="{{ old('end_date', isset($endDate) ? $endDate->toDateString() : '') }}" 
                                    
                                >
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 60px;">Filter</button>
                        </div>
                    </form>

                   <!-- PDF Download Form -->
                    <form id="pdfForm" action="{{ route('admin.user-reports.download') }}" method="POST" class="d-flex align-items-center gap-2">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ isset($startDate) ? $startDate->toDateString() : '' }}">
                        <input type="hidden" name="end_date" value="{{ isset($endDate) ? $endDate->toDateString() : '' }}">
                        <input type="hidden" name="chart_image" id="chartImage">
                        <button id="pdfDownloadButton" type="submit" class="btn btn-danger">Download PDF</button>
                    </form>
                </div>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="list-unstyled mb-0">
                        @foreach ($errors->all() as $error)
                            <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Report Results -->
            @isset($newUsersCount)
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="text-muted mb-0">
                            Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </h6>
                        {{-- <div class="badge bg-primary p-2">
                            Total New Users: {{ number_format($newUsersCount) }}
                        </div> --}}
                    </div>

                    <!-- Chart Container -->
                    <div class="wg-box mb-4">
                        <div id="user-registrations-chart" style="min-height: 400px;"></div>
                    </div>

                    <!-- Users Table -->
                    @if($newUsersCount > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        {{-- <th scope="col" class="text-center">#</th> --}}
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Registration Date</th>
                                        <!-- <th scope="col" class="text-center">Actions</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($newUsers as $user)
                                        <tr>
                                            {{-- <td class="text-center">{{ $user->id }}</td> --}}
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3 bg-light rounded-circle">
                                                        <span class="text-primary">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    {{ $user->name }}
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $user->created_at->format('M d, Y h:i A') }}
                                                </span>
                                            </td>
                                            <!-- <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td> -->
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No new users registered in the selected date range.
                        </div>
                    @endif
                </div>
            @endisset
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.main-content-inner {
    padding: 20px;
}

.gap20 {
    gap: 20px;
}

.gap10 {
    gap: 10px;
}

.mb-27 {
    margin-bottom: 27px;
}

.text-tiny {
    font-size: 0.875rem;
}

.wg-box {
    background: #fff; /* Ensure the background is white */
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    padding: 20px; /* Additional padding */
}

.avatar-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.breadcrumbs {
    list-style: none;
    padding: 0;
    margin: 0;
}

.breadcrumbs li {
    display: flex;
    align-items: center;
}

.breadcrumbs i {
    font-size: 12px;
    margin: 0 5px;
}
</style>
@endpush

@push('scripts')
@if(isset($chartData))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            chart: {
                type: 'area',
                height: 400,
                fontFamily: 'inherit',
                toolbar: {
                    show: false,
                }
            },
            series: [{
                name: 'New Users',
                data: @json($chartData['counts'])
            }],
            xaxis: {
                categories: @json($chartData['dates']),
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3,
                    stops: [0, 90, 100]
                }
            },
        };

        var chart = new ApexCharts(document.querySelector("#user-registrations-chart"), options);
        chart.render();

        // Capture chart as Base64 and attach it to the form
        document.getElementById('pdfForm').addEventListener('submit', function (event) {
            event.preventDefault();
            chart.dataURI().then(({ imgURI }) => {
                document.getElementById('chartImage').value = imgURI;
                this.submit();
            });
        });
    });
</script>
@endif
@endpush
