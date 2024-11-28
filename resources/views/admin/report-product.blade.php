@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Product Reports</h3>
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
                    <div class="text-tiny">Product Reports</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Product Reports</h5>
                <!-- Download PDF button -->
                <form action="{{ route('admin.report-product.download') }}" method="GET" class="d-flex">
                    <button type="submit" class="btn btn-danger">Download PDF</button>
                </form>
            </div>

            <!-- Most Frequent Products Table -->
            <div class="wg-table table-all-user mb-4">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th class="text-center">Total Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mostFrequentLabels as $index => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-center">{{ $mostFrequentData[$index] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Least Bought Products Table -->
            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th class="text-center">Total Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leastBoughtLabels as $index => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-center">{{ $leastBoughtData[$index] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 12px;
        text-align: left;
    }

    .table td {
        white-space: nowrap;
    }

    .text-tiny {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    .wg-table .table {
        margin-top: 20px;
    }

    .wg-table .table th {
        background-color: #f8f9fa;
    }

    .divider {
        margin: 20px 0;
        border-bottom: 1px solid #ddd;
    }
</style>
@endpush
