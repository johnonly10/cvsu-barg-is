@extends('layouts.admin')
@section('content')
<style>
    /* Existing styles */
    .badge-success {
        background-color: #28a745;
          display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s;
        margin-bottom: 2px;
    }
    .badge-warning {
        background-color: #ffc107;
           display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s;
        margin-bottom: 2px;
    }
    .badge-danger {
        background-color: #dc3545;
           display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s;
        margin-bottom: 2px;
    }

    /* Enhanced tooltip styles */
    .variant-cell {
        position: relative;
        cursor: pointer;
    }

    .variant-cell:hover .variant-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .variant-tooltip {
        position: absolute;
        top: -100%;
        left: 40%;
        transform: translateX(-100%) translateY(-100px);
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        padding: 0 10px;
        min-width: 150px;
        opacity: 0;
        border: 1px solid;
        transition: all 0.2s ease-in-out;
        z-index: 100;
        margin-top: 19px;
        pointer-events: none;
    }


    .variant-group {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .variant-group:last-child {
        border-bottom: none;
    }

    .variant-attribute-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }

    .variant-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.9em;
        color: #666;
    }

    .variant-value {
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .variant-value:hover {
        color: #28a745;  /* Green on hover */
    }

    .variant-tooltip {
        display: none;  /* Hide tooltip by default */
    }

    .variant-cell:hover .variant-tooltip {
        display: block;  /* Show tooltip on hover */
    }

    tr:hover {
        background-color: #f1f1f1; 
    }
    .table {
    table-layout: fixed;
    width: 100%;
}

.table th, .table td {
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>All Products</h3>
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
                    <div class="text-tiny">All Products</div>
                </li>
            </ul>
        </div>
        
        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search">
                        <fieldset class="name">
                            <input type="text" id="product-search" placeholder="Search here..." class="" name="name"
                                tabindex="2" value="" aria-required="true" required="">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>
                <a class="tf-button style-1 w208" href="{{route('admin.categories')}}"><i
                        class="icon-plus"></i>Add Categories</a>
                <a class="tf-button style-1 w208" href="{{route('admin.product-attribute-add')}}"><i
                        class="icon-plus"></i>Add Variations</a>
                <a class="tf-button style-1 w208" href="{{route('admin.product.add')}}"><i
                        class="icon-plus"></i>Add new</a>
                <a class="tf-button style-1 w208" href="{{route('admin.archived-products')}}"><i class="icon-archive"></i> Archived Products</a>
            </div>
            <div class="table-responsive">
                @if (Session::has('status'))
                <p class="alert alert-success">{{Session::get('status')}}</p>
                @endif
              <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                            <th style="width: 5%">Name</th>
                            <th style="width: 3%">Quantity</th>
                            <th style="width: 3%">Price</th>
                            <th style="width: 4%">Category</th>
                            <th style="width: 9%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                    @php
                        $groupedAttributes = [];
                        $uniqueAttributes = [];
                        foreach ($product->attributeValues as $value) {
                            if ($value->productAttribute) {
                                $attributeId = $value->product_attribute_id;
                                $groupedAttributes[$attributeId][] = $value;
                                if (!isset($uniqueAttributes[$attributeId])) {
                                    $uniqueAttributes[$attributeId] = $value->productAttribute;
                                }
                            }
                        }

                        $currentStock = $product->attributeValues->count() > 0
                            ? $product->attributeValues->sum('quantity')
                            : $product->quantity;

                        // Set stock status based on quantities
                        if ($currentStock == 0) {
                            $stockStatus = 'Out of Stock';
                            $badgeClass = 'badge-danger';
                        } elseif ($currentStock <= $product->outofstock_quantity) {
                            $stockStatus = 'Low Stock';
                            $badgeClass = 'badge-danger';
                        } elseif ($currentStock <= $product->reorder_quantity) {
                            $stockStatus = 'Reorder Level';
                            $badgeClass = 'badge-warning';
                        } else {
                            $stockStatus = 'In Stock';
                            $badgeClass = 'badge-success';
                        }

                    @endphp
                    <tr>
                        {{-- <td style="width: 3%">{{ $product->id }}</td> --}}
                        <td class="pname" style="width: 100%">
                            <div class="image">
                                <img src="{{ asset('uploads/products/thumbnails') }}/{{ $product->image }}" alt="{{ $product->name }}" class="image">
                            </div>
                            <div class="name">
                                <a href="#" class="body-title-2">{{ $product->name }}</a>
                                <div class="text-tiny mt-3">
                                    <span class="badge {{ $badgeClass }}">{{ $stockStatus }}</span>
                                </div>
                            </div>
                        </td>
                                
                        {{-- Quantity column with detailed variant tooltip --}}
                        <td class="variant-cell" style="width: 10%">
                            @if ($product->attributeValues->count() > 0)
                                <span class="variant-value" data-bs-content="Quantity: {{ $product->attributeValues->first()->quantity }}">
                                    {{  $product->attributeValues->sum('quantity');}}
                                </span>
                                <div class="variant-tooltip">
                                    <!-- Tooltip content for variant quantities -->
                                    @foreach ($groupedAttributes as $attributeId => $variants)
                                        <div class="variant-group">
                                            <div class="variant-attribute-name">{{ $uniqueAttributes[$attributeId]->name }}</div>
                                            @foreach ($variants as $variant)
                                                <div class="variant-row tooltip-highlight">
                                                    <span class="variant-value">{{ $variant->value }}</span>
                                                    <span class="variant-detail">{{ $variant->quantity }} units</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{ $product->quantity }}
                            @endif
                        </td>

                        <td class="variant-cell" style="width: 10%">
                            @if ($product->attributeValues->count() > 0)
                                <span class="variant-value" data-bs-content="Price: &#8369;{{ number_format($product->attributeValues->first()->price, 2) }}">
                                    &#8369;{{ number_format($product->attributeValues->first()->price, 2) }}
                                </span>
                                <div class="variant-tooltip">
                                    <!-- Tooltip content for variant prices -->
                                    @foreach ($groupedAttributes as $attributeId => $variants)
                                        <div class="variant-group">
                                            <div class="variant-attribute-name">{{ $uniqueAttributes[$attributeId]->name }}</div>
                                            @foreach ($variants as $variant)
                                                <div class="variant-row tooltip-highlight">
                                                    <span class="variant-value">{{ $variant->value }}</span>
                                                    <span class="variant-detail">&#8369;{{ number_format($variant->price, 2) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                &#8369;{{ number_format($product->price, 2) }}
                            @endif
                        </td>

                        
                        <td style="width: 10%">
                            @if ($product->category)
                                @if ($product->category->parent)
                                    {{ $product->category->parent->name }} &raquo; {{ $product->category->name }}
                                @else
                                    {{ $product->category->name }}
                                @endif
                            @else
                                No Category
                            @endif
                        </td>
                        
                
                        <td style="width: 7%">
                            <div class="list-icon-function">
                                <a href="{{ route('admin.product.edit', ['id' => $product->id]) }}">
                                    <div class="item edit">
                                        <i class="icon-edit-3"></i>
                                    </div>
                                </a>
                                <form action="{{ route('admin.product.archive', ['id' => $product->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="item text-warning archive">
                                        <i class="icon-archive"></i> 
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                
            </table>
            
            </div>

            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{$products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
    $(function () {
    function productManager() {
        const tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');

        // Initialize tooltips and hover effects for variants
        function initVariantTooltips() {
            $('.variant-value').hover(function () {
                const $this = $(this);
                const content = $this.attr('data-bs-content');
                tooltip.text(content).fadeIn('fast');
            }, function () {
                tooltip.hide();
            }).mousemove(function (e) {
                tooltip.css({
                    top: e.pageY + 10 + 'px',
                    left: e.pageX + 10 + 'px',
                });
            });
        }

        // Archive button confirmation
        function initArchiveButton() {
            $('.archive').on('click', function (e) {
                e.preventDefault();
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to archive this product?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, archive it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }

        // Fetch and render products
        function fetchProducts(query = '') {
            $.ajax({
                url: "{{ route('admin.products.search') }}",
                type: "GET",
                data: { query: query },
                success: function (data) {
                    renderProductTable(data);
                },
                error: function () {
                    console.error("Failed to fetch products.");
                },
            });
        }

        // Render products in the table
        function renderProductTable(products) {
            const tableBody = $('tbody');
            tableBody.empty();

            if (products.length > 0) {
                products.forEach((product) => {
                    const category = product.category
                        ? (product.category.parent
                            ? `${product.category.parent.name} &raquo; ${product.category.name}`
                            : product.category.name)
                        : "No Category";

                    const badgeClass = product.badge_class || "badge-secondary";
                    const stockStatus = product.stock_status || "Unknown";
                    let variantPricesHtml = '';

                    if (product.variant_prices && product.variant_prices.length > 0) {
                        product.variant_prices.forEach((variant) => {
                            variantPricesHtml += `
                                <div class="variant-row">
                                    <span class="variant-value" data-bs-content="&#8369;${parseFloat(variant.price).toFixed(2)}">${variant.value}</span>
                                    <span class="variant-price">&#8369;${parseFloat(variant.price).toFixed(2)}</span>
                                </div>
                            `;
                        });
                    }

                    tableBody.append(`
                       @foreach ($products as $product)
                    @php
                        $groupedAttributes = [];
                        $uniqueAttributes = [];
                        foreach ($product->attributeValues as $value) {
                            if ($value->productAttribute) {
                                $attributeId = $value->product_attribute_id;
                                $groupedAttributes[$attributeId][] = $value;
                                if (!isset($uniqueAttributes[$attributeId])) {
                                    $uniqueAttributes[$attributeId] = $value->productAttribute;
                                }
                            }
                        }

                        $currentStock = $product->attributeValues->count() > 0
                            ? $product->attributeValues->sum('quantity')
                            : $product->quantity;

                        // Set stock status based on quantities
                        if ($currentStock == 0) {
                            $stockStatus = 'Out of Stock';
                            $badgeClass = 'badge-danger';
                        } elseif ($currentStock <= $product->outofstock_quantity) {
                            $stockStatus = 'Low Stock';
                            $badgeClass = 'badge-danger';
                        } elseif ($currentStock <= $product->reorder_quantity) {
                            $stockStatus = 'Reorder Level';
                            $badgeClass = 'badge-warning';
                        } else {
                            $stockStatus = 'In Stock';
                            $badgeClass = 'badge-success';
                        }

                    @endphp
                    <tr>
                        {{-- <td style="width: 3%">{{ $product->id }}</td> --}}
                        <td class="pname" style="width: 100%">
                            <div class="image">
                                <img src="{{ asset('uploads/products/thumbnails') }}/{{ $product->image }}" alt="{{ $product->name }}" class="image">
                            </div>
                            <div class="name">
                                <a href="#" class="body-title-2">{{ $product->name }}</a>
                                <div class="text-tiny mt-3">
                                    <span class="badge {{ $badgeClass }}">{{ $stockStatus }}</span>
                                </div>
                            </div>
                        </td>
                                
                        {{-- Quantity column with detailed variant tooltip --}}
                        <td class="variant-cell" style="width: 10%">
                            @if ($product->attributeValues->count() > 0)
                                <span class="variant-value" data-bs-content="Quantity: {{ $product->attributeValues->first()->quantity }}">
                                    {{  $product->attributeValues->sum('quantity');}}
                                </span>
                                <div class="variant-tooltip">
                                    <!-- Tooltip content for variant quantities -->
                                    @foreach ($groupedAttributes as $attributeId => $variants)
                                        <div class="variant-group">
                                            <div class="variant-attribute-name">{{ $uniqueAttributes[$attributeId]->name }}</div>
                                            @foreach ($variants as $variant)
                                                <div class="variant-row tooltip-highlight">
                                                    <span class="variant-value">{{ $variant->value }}</span>
                                                    <span class="variant-detail">{{ $variant->quantity }} units</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{ $product->quantity }}
                            @endif
                        </td>

                        <td class="variant-cell" style="width: 10%">
                            @if ($product->attributeValues->count() > 0)
                                <span class="variant-value" data-bs-content="Price: &#8369;{{ number_format($product->attributeValues->first()->price, 2) }}">
                                    &#8369;{{ number_format($product->attributeValues->first()->price, 2) }}
                                </span>
                                <div class="variant-tooltip">
                                    <!-- Tooltip content for variant prices -->
                                    @foreach ($groupedAttributes as $attributeId => $variants)
                                        <div class="variant-group">
                                            <div class="variant-attribute-name">{{ $uniqueAttributes[$attributeId]->name }}</div>
                                            @foreach ($variants as $variant)
                                                <div class="variant-row tooltip-highlight">
                                                    <span class="variant-value">{{ $variant->value }}</span>
                                                    <span class="variant-detail">&#8369;{{ number_format($variant->price, 2) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                &#8369;{{ number_format($product->price, 2) }}
                            @endif
                        </td>

                        
                        <td style="width: 10%">
                            @if ($product->category)
                                @if ($product->category->parent)
                                    {{ $product->category->parent->name }} &raquo; {{ $product->category->name }}
                                @else
                                    {{ $product->category->name }}
                                @endif
                            @else
                                No Category
                            @endif
                        </td>
                        
                
                        <td style="width: 7%">
                            <div class="list-icon-function">
                                <a href="{{ route('admin.product.edit', ['id' => $product->id]) }}">
                                    <div class="item edit">
                                        <i class="icon-edit-3"></i>
                                    </div>
                                </a>
                                <form action="{{ route('admin.product.archive', ['id' => $product->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="item text-warning archive">
                                        <i class="icon-archive"></i> 
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    `);
                });
            } else {
                tableBody.append(`
                    <tr>
                        <td colspan="5" class="text-center">No products found with the search query.</td>
                    </tr>
                `);
            }

            initVariantTooltips(); // Reinitialize tooltips after rendering
            initArchiveButton(); // Reinitialize archive buttons after rendering
        }

        // Search functionality
        $('#product-search').on('input', function () {
            fetchProducts($(this).val());
        });

        // Clear search
        $('#product-search').on('clear', function () {
            fetchProducts();
        });

        // Fetch all products on initialization
        fetchProducts();
    }

    // Initialize product manager
    productManager();
});




   
</script>

@endpush
