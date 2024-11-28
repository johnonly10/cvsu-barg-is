<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        h1, h2 {
            text-align: center;
        }
        .text-tiny {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <h1>Product Report</h1>
    <p style="text-align: center; font-size: 14px;">
        Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
    </p> 

    <!-- Most Frequent Products Section -->
    <h2>Most Frequent Products</h2>

    
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th class="text-center">Total Orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mostFrequentLabels as $index => $label)
                <tr>
                    <td>{{ $label ?? 'No Product' }}</td>
                    <td class="text-center">{{ $mostFrequentData[$index] ?? '0' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Least Bought Products Section -->
    <h2>Least Bought Products</h2>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th class="text-center">Total Orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leastBoughtLabels as $index => $label)
                <tr>
                    <td>{{ $label ?? 'No Product' }}</td>
                    <td class="text-center">{{ $leastBoughtData[$index] ?? '0' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
