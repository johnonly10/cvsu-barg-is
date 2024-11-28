<!DOCTYPE html>
<html>
<head>
    <title>User Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .container {
            padding: 20px;
            width: 100%;
            max-width: 800px;
            margin: auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .chart-container {
            text-align: center;
            margin-top: 20px;
        }
        .summary {
            margin-top: 30px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Authentication Report</h2>
        <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
        
        <!-- Chart Section -->
        @if(isset($chartImage) && $chartImage)
        <div class="chart-container">
            <img src="{{ $chartImage }}" alt="User Registration Chart" style="max-width: 100%; height: auto;">
        </div>
        @endif

        <!-- Summary Section -->
        <div class="summary">
            <p><strong>Total New Users:</strong> {{ number_format($newUsersCount) }}</p>
        </div>

        <!-- Users Table -->
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registration Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($newUsers as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('M d, Y h:i A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
