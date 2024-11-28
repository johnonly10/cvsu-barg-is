<!DOCTYPE html>
<html>
<head>
    <title>Inventory Report PDF</title>
    <style>
        body {
            font-family: sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .badge {
            padding: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        .bg-success {
            background-color: #28a745;
            color: #fff;
        }
        .bg-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .bg-danger {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Inventory Report</h2>
    <p style="text-align: center; font-size: 14px;">
        Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}<br>
        Date Range:
        @if ($startDate && $endDate)
            {{ $startDate }} to {{ $endDate }}
        @else
            N/A
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock Status</th>
                <th>Current Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <!-- Existing table row content -->
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name }}</td>
                <td>
                    @if($product->attributeValues->isNotEmpty())
                        {{ $product->attributeValues->first()->price }}
                    @else
                        {{ $product->price }}
                    @endif
                </td>
                <td>
                    @php
                        $currentStock = $product->attributeValues->isNotEmpty()
                            ? $product->attributeValues->sum('quantity')
                            : $product->current_stock;
                    @endphp

                    @if($currentStock <= $product->outofstock_quantity)
                        <span class="badge bg-danger">Low Stock</span>
                    @elseif($currentStock <= $product->reorder_quantity)
                        <span class="badge bg-warning">Reorder Level</span>
                    @else
                        <span class="badge bg-success">In Stock</span>
                    @endif
                </td>
                <td>{{ $currentStock }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
