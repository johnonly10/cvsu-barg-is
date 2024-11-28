<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        .chart-container {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sales Report</h2>
        <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>

        <!-- Chart Section -->
        @if(isset($chartImage) && $chartImage)
        <div class="chart-container">
            <img src="{{ $chartImage }}" alt="Sales Chart" style="max-width: 100%; height: auto;">
        </div>
        @endif

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Sales</th>
                    <th>Reserved Sales</th>
                    <th>Picked Up Sales</th>
                    <th>Canceled Sales</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chartData['dates'] as $index => $date)
                <tr>
                    <td>{{ $date }}</td>
                    <td>${{ number_format($chartData['total_sales'][$index], 2) }}</td>
                    <td>${{ number_format($chartData['reserved_sales'][$index], 2) }}</td>
                    <td>${{ number_format($chartData['pickedup_sales'][$index], 2) }}</td>
                    <td>${{ number_format($chartData['canceled_sales'][$index], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
