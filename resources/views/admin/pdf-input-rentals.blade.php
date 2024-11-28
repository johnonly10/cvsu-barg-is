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
                <li><div class="text-tiny">Rental Sales Report</div></li>
            </ul>
        </div>

        <!-- Main Content Card -->
        <div class="wg-box p-4 bg-light shadow-sm rounded-lg">
            <!-- Header Section with Controls -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Rental Sales Report</h5>
                
                <div class="d-flex gap-2">
                    <!-- Date Range Form -->
                    <form action="{{ route('admin.generate-input-rentals-reports') }}" method="POST" id="rentals-form" 
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
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>

                      <!-- PDF Download Form -->
                <form id="pdfForm" action="{{ route('admin.download-input-rentals-reports') }}" method="POST" class="d-flex align-items-center gap-2">
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
            @isset($reportData)
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="text-muted mb-0">
                            Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </h6>
                    </div>

                    <!-- Chart Container -->
                    <div class="wg-box mb-4">
                        <div id="rentals-chart" style="min-height: 400px;"></div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6>Total Pending: ${{ number_format($reportData['pending_total'], 2) }}</h6>
                            <h6>Total Full Payments: ${{ number_format($reportData['full_payment_total'], 2) }}</h6>
                        </div>
                        <div>
                            <h6>Total Canceled: ${{ number_format($reportData['canceled_total'], 2) }}</h6>
                            <h6>Total Sales: ${{ number_format($reportData['total_sales'], 2) }}</h6>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No sales data available for the selected date range.
                </div>
            @endisset
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(isset($reportData))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            chart: {
                type: 'bar',
                height: 400,
                fontFamily: 'inherit',
                toolbar: {
                    show: true,
                }
            },
            series: [
                { name: 'Pending', data: @json($reportData['pending']) },
                { name: 'Full Payments', data: @json($reportData['full_payment']) },
                { name: 'Canceled', data: @json($reportData['canceled']) },
            ],
            xaxis: {
                categories: @json($reportData['dates']),
            },
        };

        var chart = new ApexCharts(document.querySelector("#rentals-chart"), options);
        chart.render();

        // Capture chart as Base64 before submitting the PDF form
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

