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
                <li><div class="text-tiny">Sales Report</div></li>
            </ul>
        </div>

        <!-- Main Content Card -->
        <div class="wg-box p-4 bg-light shadow-sm rounded-lg">
            <!-- Header Section with Controls -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Sales Report</h5>
                
                <div class="d-flex gap-2">
                    <!-- Date Range Form -->
                    <form action="{{ route('admin.generate-input-sales') }}" method="POST" id="sales-form" 
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
                                    class="form-select me-3"
                                    style="width: 140px;"
                                    value="{{ old('end_date', isset($endDate) ? $endDate->toDateString() : '') }}" 
                                >
                            </div>
                            <button type="submit" class="btn btn-primary me-2" style="width: 60px;">Filter</button>
                        </div>
                    </form>

                    <!-- PDF Download Form -->
                    <form action="{{ route('admin.download-input-sales') }}" method="POST" id="download-form" class="d-flex align-items-center gap-2">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ old('start_date', isset($startDate) ? $startDate->toDateString() : '') }}">
                        <input type="hidden" name="end_date" value="{{ old('end_date', isset($endDate) ? $endDate->toDateString() : '') }}">
                        <input type="hidden" name="chart_image" id="chart_image">
                        <button type="submit" class="btn btn-danger">Download PDF</button>
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
            @isset($chartData)
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="text-muted mb-0">
                            Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </h6>
                    </div>

                    <!-- Chart Container -->
                    <div class="wg-box mb-4">
                        <div id="sales-chart" style="min-height: 400px;"></div>
                    </div>

                    <!-- Sales Summary -->
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h6>Total Orders: {{ number_format($chartData['total_orders']) }}</h6>
                            <h6>Total Reserved Sales: ${{ number_format($chartData['reserved_sales_total'], 2) }}</h6>
                        </div>
                        <div>
                            <h6>Total Picked Up Sales: ${{ number_format($chartData['pickedup_sales_total'], 2) }}</h6>
                            <h6>Total Canceled Sales: ${{ number_format($chartData['canceled_sales_total'], 2) }}</h6>
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
@if(isset($chartData))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            chart: {
                type: 'bar',
                height: 500,
                fontFamily: 'inherit',
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    columnWidth: '40%', 
                }
            },
            series: [
                { name: 'Total Sales', data: @json($chartData['total_sales']) },
                { name: 'Reserved Sales', data: @json($chartData['reserved_sales']) },
                { name: 'Picked Up Sales', data: @json($chartData['pickedup_sales']) },
                { name: 'Canceled Sales', data: @json($chartData['canceled_sales']) }
            ],
            xaxis: {
                categories: @json($chartData['dates']),
                title: { text: 'Date', style: { fontSize: '14px', fontWeight: 500 } },
            },
            yaxis: {
                title: { style: { fontSize: '14px', fontWeight: 500 } },
                labels: { formatter: function (val) { return `${val.toFixed(2)}`; } }
            },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3,
                    stops: [0, 90, 100]
                }
            },
            tooltip: {
                y: { formatter: function (val) { return `$${val.toFixed(2)}`; } }
            },
            grid: { borderColor: '#f1f1f1', padding: { top: 0, right: 20, bottom: 0, left: 20 } }
        };

        var chart = new ApexCharts(document.querySelector("#sales-chart"), options);
        chart.render();

        // Capture the chart as Base64 before submitting the download form
        document.getElementById('download-form').addEventListener('submit', function (event) {
            event.preventDefault();
            chart.dataURI().then(({ imgURI }) => {
                document.getElementById('chart_image').value = imgURI; // Pass the chart image
                this.submit(); // Submit the form
            });
        });
    });
</script>
@endif
@endpush


