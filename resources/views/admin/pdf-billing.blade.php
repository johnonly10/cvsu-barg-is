<!DOCTYPE html>
<html>
<head>
    <title>Billing Statements</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px; /* Add some margin */
        }
        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 12px; /* Adjust font size for PDF */
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h3 style="text-align: center;">Billing Statements</h3>
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
                <th>Order No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Total</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->user->name }}</td>
                <td>{{ $order->user->email }}</td>
                <td>{{ number_format($order->total, 2) }}</td>
                <td>{{ $order->created_at->format('F d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
