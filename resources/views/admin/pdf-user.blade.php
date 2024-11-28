<!DOCTYPE html>
<html>
<head>
    <title>User Report PDF</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center; 
        }
        h1, h3 {
            text-align: center; 
        }
        .chart-container {
            display: block;
            margin: 20px auto;
            width: 100%;
            max-width: 700px; 
        }
        img {
            display: block;
            margin: 0 auto;
            width: 100%;/
            max-width: 700px; 
            height: auto;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>User Registrations Report for {{ $selectedMonth }} {{ $selectedYear }}</h1>

    <div class="chart-container">
        <h3>Monthly Registered Accounts</h3>
        <img src="{{ $monthlyChartImage }}" alt="Monthly User Registrations Chart">
    </div>

    <div class="chart-container">
        <h3>Weekly Registered Accounts</h3>
        <img src="{{ $weeklyChartImage }}" alt="Weekly User Registrations Chart">
    </div>

    <div class="chart-container">
        <h3>Daily Registered Accounts</h3>
        <img src="{{ $dailyChartImage }}" alt="Daily User Registrations Chart">
    </div>

    <!-- <h3>Recently Registered Users</h3>
    <ul>
        @foreach ($recentUsers as $user)
            <li>{{ $user->name }} - Registered on {{ $user->created_at->format('M d, Y') }}</li>
        @endforeach
    </ul> -->
</body>
</html>
