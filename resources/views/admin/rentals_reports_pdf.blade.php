<!DOCTYPE html>
<html>
<head>
    <title>Rentals Reports PDF</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
        }
        h2 { 
            text-align: center; 
            margin-bottom: 20px; 
            font-size: 24px;
        }
        .summary {
            font-size: 1.1rem;
            margin-bottom: 30px;
            text-align: center;
        }
        .summary p {
            margin: 5px 0;
        }
        .chart {
            text-align: center;
            margin-bottom: 40px;
        }
        .chart-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        img {
            width: 100%;
            height: 200px;
            max-width: none;
        }
    </style>
</head>
<body>
    <h2>Rentals Reports</h2>

    {{-- <div class="summary">
        <p><strong>Total Payment Amount:</strong> ${{ number_format($totalAmounts['total_payment'], 2) }}</p>
        <p><strong>Payment Pending Amount:</strong> ${{ number_format($totalAmounts['pending_amount'], 2) }}</p>
        <p><strong>Payment Completed Amount:</strong> ${{ number_format($totalAmounts['completed_amount'], 2) }}</p>
        <p><strong>Payment Canceled Amount:</strong> ${{ number_format($totalAmounts['canceled_amount'], 2) }}</p>
    </div> --}}

    <div class="chart">
        <div class="chart-title">Monthly Rentals Reports</div>
        @if($monthlyImage)
            <img src="{{ $monthlyImage }}" alt="Monthly Rentals Chart">
        @else
            <p>No data available for Monthly Rentals.</p>
        @endif
    </div>
    
    <div class="chart">
        <div class="chart-title">Weekly Rentals Reports</div>
        @if($weeklyImage)
            <img src="{{ $weeklyImage }}" alt="Weekly Rentals Chart">
        @else
            <p>No data available for Weekly Rentals.</p>
        @endif
    </div>
    
    <div class="chart">
        <div class="chart-title">Daily Rentals Reports</div>
        @if($dailyImage)
            <img src="{{ $dailyImage }}" alt="Daily Rentals Chart">
        @else
            <p>No data available for Daily Rentals.</p>
        @endif
    </div>
</body>
</html>
