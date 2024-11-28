{{-- resources/views/admin/rentals_reports_name_pdf.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <title>Rentals Reports by Name PDF</title>
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
            height: auto;
            max-width: none;
        }
        .rental-summary {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .rental-summary .dot {
            height: 15px;
            width: 15px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        /* Increase image sizes specifically for the PDF */
        @media print {
            .chart img {
                width: 100%;
                height: auto;
            }
        }
        /* Additional styling to make images bigger */
        .chart img {
            max-height: 800px; /* Increase max-height as needed */
        }
    </style>
</head>
<body>
    <h2>Rentals Reports by Name</h2>

    {{-- <div class="summary">
        @foreach($reservationsPerRental as $rentalName => $count)
            <div class="rental-summary">
                <div class="dot" style="background-color: {{ $rentalColors[$rentalName] ?? '#000' }};"></div>
                <p><strong>{{ $rentalName }}:</strong> {{ number_format($count, 0) }} Reservations</p>
            </div>
        @endforeach
    </div> --}}

    <div class="chart">
        <div class="chart-title">Monthly Reservations by Rental Name</div>
        @if($monthlyImage)
            <img src="{{ $monthlyImage }}" alt="Monthly Reservations Chart">
        @else
            <p>No data available for Monthly Reservations.</p>
        @endif
    </div>
    
    <div class="chart">
        <div class="chart-title">Weekly Reservations by Rental Name</div>
        @if($weeklyImage)
            <img src="{{ $weeklyImage }}" alt="Weekly Reservations Chart">
        @else
            <p>No data available for Weekly Reservations.</p>
        @endif
    </div>
    
    <div class="chart">
        <div class="chart-title">Daily Reservations by Rental Name</div>
        @if($dailyImage)
            <img src="{{ $dailyImage }}" alt="Daily Reservations Chart">
        @else
            <p>No data available for Daily Reservations.</p>
        @endif
    </div>
</body>
</html>
