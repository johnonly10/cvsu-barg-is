<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        /* Add your styles here for the PDF layout */
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>Sales Report</h1>

    <h2>Monthly Earned Sales</h2>
    <img src="{{ $monthlySalesImg }}" alt="Monthly Sales Chart">

    <h2>Weekly Earnings</h2>
    <img src="{{ $weeklySalesImg }}" alt="Weekly Sales Chart">

    <h2>Daily Earned Sales</h2>
    <img src="{{ $dailySalesImg }}" alt="Daily Sales Chart">

    <!-- Display other relevant data -->
    <h3>Recent Orders:</h3>
    <ul>
        @foreach($orders as $order)
            <li>{{ $order->id }} - {{ $order->total }}</li>
        @endforeach
    </ul>
</body>
</html>
