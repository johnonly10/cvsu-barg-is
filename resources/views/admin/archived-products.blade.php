@extends('layouts.admin')

@section('content')
    <style>
        /* Base Styles */
        :root {
            --primary-color: #28a745;
            --danger-color: #dc3545;
            --hover-opacity: 0.9;
            --transition-speed: 0.3s;
            --border-radius: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
        }

        /* Badge Styles */
        .badge {
            padding: 0.25em 0.4em;
            border-radius: var(--border-radius);
            font-size: 0.75em;
            font-weight: 600;
            transition: opacity var(--transition-speed) ease;
        }

        .badge:hover {
            opacity: var(--hover-opacity);
        }

        .badge-success {
            background-color: var(--primary-color);
            color: #fff;
        }

        .badge-danger {
            background-color: var(--danger-color);
            color: #fff;
        }

        /* Layout & Spacing */
        .main-content-wrap {
            padding: var(--spacing-lg);
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .header-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid #eee;
        }

        /* Breadcrumbs */
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-size: 0.875rem;
            color: #6c757d;
        }

        .breadcrumbs a {
            color: inherit;
            text-decoration: none;
            transition: color var(--transition-speed) ease;
        }

        .breadcrumbs a:hover {
            color: var(--primary-color);
        }

        /* Search & Actions */
        .actions-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            gap: var(--spacing-md);
        }

        .search-form {
            flex-grow: 1;
            max-width: 400px;
            position: relative;
        }

        .search-form input {
            width: 100%;
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            transition: border-color var(--transition-speed) ease;
        }

        .search-form input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .search-form button {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
        }

        /* Table Styles */
        .table-container {
            background: #fff;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .table {
            width: 100%;
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }

        .table td, .table th {
            padding: var(--spacing-md);
            vertical-align: middle;
        }

        /* Product Card in Table */
        .product-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .product-image {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius);
            object-fit: cover;
        }

        .product-details {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .product-name {
            font-weight: 600;
            color: #2d3748;
        }

        /* Bulk Actions */
        .bulk-actions {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md) 0;
        }

        .bulk-actions label {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin: 0;
        }

        /* Buttons */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all var(--transition-speed) ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .btn-success {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
        }

        .btn-success:hover:not(:disabled) {
            background-color: #218838;
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            padding-top: var(--spacing-lg);
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <!-- Header Section -->
            <div class="header-section">
                <h3 class="text-2xl font-bold">Archived Products</h3>
                <ul class="breadcrumbs">
                    <li><a href="{{ route('admin.index') }}">Dashoard</a></li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>Archived Products</li>
                </ul>
            </div>

            <!-- Search & Actions -->
            <div class="actions-wrapper">
                <form class="search-form" method="GET" action="{{ route('admin.archived-products') }}">
                    <input type="text" 
                           placeholder="Search archived products..." 
                           name="search"
                           value="{{ request('search') }}"
                           aria-label="Search products">
                    <button type="submit"><i class="icon-search"></i></button>
                </form>
                <a class="tf-button style-1 w208" href="{{ route('admin.products') }}"> <i class="icon-products"></i> All Products</a>
            </div>

            <!-- Bulk Actions -->
            <div class="bulk-actions">
                <label>
                    <input type="checkbox" id="select-all" class="form-checkbox">
                    <span>Select All</span>
                </label>
                <span id="selected-count" class="text-sm text-gray-600">0 selected</span>
                <button class="btn btn-success" id="restore-btn" disabled>
                    <i class="icon-restore"></i>
                    <span>Restore</span>
                </button>
            </div>

            @if (Session::has('status'))
                <div class="alert alert-success mb-4" role="alert">
                    {{ Session::get('status') }}
                </div>
            @endif

            <!-- Products Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>Product</th>
                            <th>Archived On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($archivedProducts as $product)
                            <tr>
                                <td>
                                    <input type="checkbox" 
                                           class="select-item form-checkbox" 
                                           data-id="{{ $product->id }}">
                                </td>
                                <td>{{ $product->id }}</td>
                                <td>
                                    <div class="product-info">
                                        <img src="{{ asset('uploads/products/thumbnails/' . $product->image) }}"
                                             alt="{{ $product->name }}"
                                             class="product-image">
                                        <div class="product-details">
                                            <span class="product-name">{{ $product->name }}</span>
                                            @php
                                            $quantity = $product->quantity;
                                            if ($product->attributeValues->count() > 0) {
                                                $firstVariant = $product->attributeValues->first();
                                                $quantity = $firstVariant->quantity ?? $product->quantity;
                                            }
                                            $stockStatus = $quantity > 0 ? 'In Stock' : 'Out of Stock';
                                        @endphp
                                        <span class="badge {{ $stockStatus === 'In Stock' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $stockStatus }}
                                        </span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->archived_at->format('F d, Y') }}</td>
                            </tr>
                        @endforeach
                        @if ($archivedProducts->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center py-8 text-gray-500">
                                    No archived products found.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $archivedProducts->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const updateButtons = () => {
                const selected = $('.select-item:checked').length;
                $('#selected-count').text(`${selected} selected`);
                $('#restore-btn').prop('disabled', selected === 0);
            };

            // Select All functionality
            $('#select-all').on('change', function() {
                $('.select-item').prop('checked', $(this).prop('checked'));
                updateButtons();
            });

            // Individual checkbox change
            $('.select-item').on('change', function() {
                const allChecked = $('.select-item:checked').length === $('.select-item').length;
                $('#select-all').prop('checked', allChecked);
                updateButtons();
            });

            // Restore Button Click
            $('#restore-btn').on('click', function() {
                const ids = $('.select-item:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length > 0) {
                    Swal.fire({
                        title: 'Restore Products',
                        text: `Do you want to restore ${ids.length} product(s)?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, restore them!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/admin/product/restore',
                                method: 'POST',
                                data: {
                                    ids: ids,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.status,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function() {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'An error occurred while restoring the products.',
                                        icon: 'error'
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush