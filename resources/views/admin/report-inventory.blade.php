@extends('layouts.admin')
@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Reports</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{route('admin.index')}}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Inventory Report</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <!-- Display Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul style="color: red;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Header Row with Date Range Inputs, "Today" Filter, Stock Status Filter, and Download PDF Button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Inventory Report</h5>
                <div class="d-flex align-items-center">
                    <!-- Date Range Filter Form -->
                    <form action="{{ route('admin.report-inventory') }}" method="GET" id="filterForm" class="form-inline d-flex align-items-center">
                         <!-- "Today" Checkbox with Text Inside -->
                         <div class="form-group mr-2 me-3">
                            <label class="checkbox-container">
                                <input type="checkbox" name="today" id="today" value="1" {{ request('today') ? 'checked' : '' }}>
                                <span class="checkmark">Today</span>
                            </label>
                        </div>
                        
                        <div class="form-group mr-2 me-3">
                            <label for="start_date" class="mr-2">Start Date:</label>
                            <div id="start_date_container">
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', request('start_date')) }}" class="form-control">
                            </div>
                        </div>
                        <div class="form-group mr-2 me-3">
                            <label for="end_date" class="mr-2">End Date:</label>
                            <div id="end_date_container">
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', request('end_date')) }}" class="form-control">
                            </div>
                        </div>

                        <!-- Stock Status Filter -->
                        <div class="form-group mr-2 me-3">
                            <label for="stock_status" class="mr-2">Stock Status:</label>
                            <select name="stock_status" id="stock_status" class="form-control">
                                <option value="">All</option>
                                <option value="instock" {{ request('stock_status') == 'instock' ? 'selected' : '' }}>In Stock</option>
                                <option value="outofstock" {{ request('stock_status') == 'outofstock' ? 'selected' : '' }}>Out of Stock</option>
                                <option value="reorder" {{ request('stock_status') == 'reorder' ? 'selected' : '' }}>Reorder Level</option>
                            </select>
                        </div>
                       
                        <div class="form-group mr-2 me-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>

                    <!-- Download PDF Form -->
                    <form action="{{ route('admin.report-inventory.pdf') }}" method="GET" class="d-flex align-items-center">
                        
                        <!-- Pass the date range parameters -->
                        <input type="hidden" name="start_date" id="download_start_date" value="{{ old('start_date', request('start_date')) }}">
                        <input type="hidden" name="end_date" id="download_end_date" value="{{ old('end_date', request('end_date')) }}">
                        <input type="hidden" name="today" value="{{ request('today') ? '1' : '' }}">
                        <!-- Pass the stock status parameter -->
                        <input type="hidden" name="stock_status" id="download_stock_status" value="{{ request('stock_status') }}">
                        <button type="submit" class="btn btn-danger ml-2">Download PDF</button>
                    </form>
                </div>
            </div>

            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: auto;">
                        <thead>
                            <tr>
                                {{-- <th>Product ID</th> --}}
                                <th class="text-center">Name</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Stock Status</th>
                                <th class="text-center">Current Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <!-- Existing table row content -->
                            <tr>
                                {{-- <td class="text-center">{{ $product->id }}</td> --}}
                                <td class="text-center">{{ $product->name }}</td>
                                <td class="text-center">{{ $product->category->name }}</td>
                                <td class="text-center">
                                    @if($product->attributeValues->isNotEmpty())
                                        {{ $product->attributeValues->first()->price }}
                                    @else
                                        {{ $product->price }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $currentStock = $product->attributeValues->isNotEmpty()
                                            ? $product->attributeValues->sum('quantity')
                                            : $product->current_stock;
                                    @endphp

                                    @if($currentStock <= $product->outofstock_quantity)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($currentStock <= $product->reorder_quantity)
                                        <span class="badge bg-warning">Reorder Level</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $currentStock }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $products->appends(request()->input())->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Existing styles */
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

    .badge.bg-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge.bg-danger {
        background-color: #dc3545;
        color: #fff;
    }

    .badge.bg-success {
        background-color: #28a745;
        color: #fff;
    }

    /* Styles for custom checkbox with text inside */
    .checkbox-container {
        display: inline-block;
        position: relative;
        padding-left: 0;
        cursor: pointer;
        user-select: none;
    }

    .checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .checkmark {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 38px;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        color: #495057;
    }

    .checkbox-container input:checked ~ .checkmark {
        background-color: #e9ecef;
    }

    /* Styles for read-only "Today" inputs */
    .readonly-input {
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script>
    // JavaScript to handle the "Today" checkbox functionality and synchronize stock_status
    document.addEventListener('DOMContentLoaded', function () {
        const todayCheckbox = document.getElementById('today');
        const startDateContainer = document.getElementById('start_date_container');
        const endDateContainer = document.getElementById('end_date_container');
        const downloadStartDate = document.getElementById('download_start_date');
        const downloadEndDate = document.getElementById('download_end_date');
        const stockStatusSelect = document.getElementById('stock_status');
        const downloadStockStatus = document.getElementById('download_stock_status');

        function updateDateInputs() {
            if (todayCheckbox.checked) {
                // Replace date inputs with text inputs displaying "Today"
                startDateContainer.innerHTML = '<input type="text" name="start_date_display" value="Today" class="form-control readonly-input" readonly>';
                endDateContainer.innerHTML = '<input type="text" name="end_date_display" value="Today" class="form-control readonly-input" readonly>';

                // Set hidden date inputs to today's date
                const today = new Date().toISOString().split('T')[0];
                downloadStartDate.value = today;
                downloadEndDate.value = today;
            } else {
                // Restore date inputs
                const startDateValue = '{{ old('start_date', request('start_date')) }}';
                const endDateValue = '{{ old('end_date', request('end_date')) }}';

                startDateContainer.innerHTML = '<input type="date" name="start_date" id="start_date" value="' + startDateValue + '" class="form-control">';
                endDateContainer.innerHTML = '<input type="date" name="end_date" id="end_date" value="' + endDateValue + '" class="form-control">';

                // Update hidden inputs for download form
                downloadStartDate.value = startDateValue;
                downloadEndDate.value = endDateValue;
            }
        }

        todayCheckbox.addEventListener('change', function () {
            updateDateInputs();
        });

        // Synchronize stock_status between forms
        stockStatusSelect.addEventListener('change', function () {
            downloadStockStatus.value = this.value;
        });

        // Initialize on page load
        updateDateInputs();
        downloadStockStatus.value = stockStatusSelect.value;
    });
</script>
@endpush
