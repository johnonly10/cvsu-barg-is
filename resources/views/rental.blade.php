@extends('layouts.app')
@section('content')
    <!-- Custom CSS for calendar modal and button -->
    <style>
    
    .calendar-modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .calendar-content {
        position: relative;
        background-color: white;
        margin: 5% auto;
        padding: 50px;
        width: 90%;
        max-width: 600px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .calendar-content button:hover{
        background-color: rgb(221, 219, 219);
    }
    .close-btn {
        position: absolute;
        top: 15px;
        right: 22px;
        background: transparent;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--bs-green);
        padding: 0 5px;
    }
    #show-calendar{
        background-color: var(--bs-green); 
    }
    #show-calendar:hover{
        background-color: var(--bs-teal)!important;
    }
    </style>

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };
        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];
        if ($currentRoute === 'shop.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
        } elseif ($currentRoute === 'shop.product.details') {
            $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
            $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];
        } elseif ($currentRoute === 'about.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];
        } elseif ($currentRoute === 'contact.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];
        } elseif ($currentRoute === 'rentals.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Rentals'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row g-4">
                @foreach ($rentals as $rental)
                <div class="col-md-6 d-flex justify-content-center">
                    <div class="card shadow-sm rental-info" style="width: 100%; max-width: 600px; transition: transform 0.3s ease; margin-top: 100px;">
                        <div class="rental-image">
                            <a href="{{ route('rentals.details', ['rental_slug' => $rental->slug]) }}">
                                <img src="{{ asset('uploads/rentals') }}/{{ $rental->image }}" alt="{{ $rental->name }}" class="card-img-top" style="height: 400px; object-fit: cover;">
                            </a>
                        </div>
                        
                        <div class="card-body text-center">
                            <h2 class="card-title">{{ $rental->name }}</h2>
                            <p><strong>Description:</strong> {{ $rental->description }} </p>
                            <!-- Prices Section -->
                            @if ($rental->internal_price && $rental->external_price)
                                <div style="display: flex; flex-direction: row; justify-content: center; gap: 5px;">
                                    <p><strong>Internal Price:</strong></p> 
                                    <h5 style="color: var(--bs-green)">₱{{ $rental->internal_price }}</h5> 
                                </div>
                                <div style="display: flex; flex-direction: row; justify-content: center; gap: 5px;">
                                    <p><strong>External Price:</strong></p> 
                                    <h5 style="color: var(--bs-green)">₱{{ $rental->external_price }}</h5> 
                                </div>
                            @elseif ($rental->price)
                                <div style="display: flex; flex-direction: row; justify-content: center; gap: 5px;">
                                    <p><strong>Price:</strong></p> 
                                    <h5 style="color: var(--bs-green)">₱{{ $rental->price }}</h5>
                                </div>
                            @else 
                                <div><p>Not applicable</p></div>
                            @endif
                            <!-- Capacity Section -->
                            @if (!$rental->capacity)
                                
                            @else 
                                <div style="display: flex; flex-direction: row; justify-content: center; gap: 5px;">
                                    <p><strong>Capacity:</strong></p> 
                                    <p>{{ $rental->capacity }} people</p>
                                </div>
                            @endif
                            
                            @if ($rental->name == 'Rolle Hall' || $rental->name == 'Swimming Pool' || $rental->name == 'International Convention Center')
                            <button type="button" class="btn text-white mt-3" id="show-calendar" onclick="openCalendarModal({{ $rental->id }})">Show Available Date</button>
                            <div id="calendarModal-{{ $rental->id }}" class="calendar-modal" style="display: none;">
                                <div class="calendar-content">
                                    <button type="button" class="close-btn" onclick="closeCalendarModal({{ $rental->id }})">
                                        <span>
                                            <i class="fa fa-xmark"></i>
                                        </span>
                                    </button>
                                    <div id="calendar-{{ $rental->id }}" class="rental-calendar"></div>
                                </div>
                            </div>
                            @endif
                            
                        </div>
                    </div>
                </div>

                <script>
                    window.onclick = function(event) {
                        var modal = document.getElementById('calendarModal-{{ $rental->id }}');
                        if (event.target == modal) {
                            closeCalendarModal({{ $rental->id }});
                        }
                    }
                </script>
            @endforeach
                </div>
            </div>
            <div class="d-flex justify-content-center w-100 mt-4">
                {{ $rentals->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </main>

    <form action="{{ route('rentals.index') }}" method="get" id="frmfilter">
        <input type="hidden" name="page" value="{{ $rentals->currentPage() }}">
    </form>
@endsection

@push('scripts')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script>
        // Open calendar modal for reservation date selection
        function openCalendarModal(rentalId) {
    document.getElementById('calendarModal-' + rentalId).style.display = 'block';

    if (!document.getElementById('calendar-' + rentalId).classList.contains('fc')) {
        var calendarEl = document.getElementById('calendar-' + rentalId);

        // Fetch reservation dates from the backend for this rental
        fetch(`/api/rentals/${rentalId}/reservations`)
            .then(response => response.json())
            .then(reservations => {
                const reservationDates = new Set();
                let events = [];

                // Process each reservation and assign it a color based on rent_status
                reservations.forEach(item => {
                    let eventColor;
                    let eventTitle;

                    switch (item.rent_status) {
                        case 'reserved':
                            eventColor = 'orange';  
                            eventTitle = 'Reserved';
                            break;
                        case 'completed':
                            eventColor = 'blue';
                            eventTitle = 'Completed';
                            break;
                        case 'canceled':
                            eventColor = 'red';
                            eventTitle = 'Canceled';
                            break;
                        case 'pending':
                            eventColor = '#ffc003';
                            eventTitle = 'Pending';
                            break;
                    }

                    events.push({
                        title: eventTitle,
                        start: item.reservation_date,
                        color: eventColor,
                        // Add a custom property to mark reserved dates
                        reserved: item.rent_status === 'reserved'
                    });

                    reservationDates.add(item.reservation_date);
                });

                // Initialize FullCalendar with event data
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: function(fetchInfo, successCallback, failureCallback) {
                        let availableEvents = []; // Reset events array for each call
                        const startDate = fetchInfo.start;
                        const endDate = fetchInfo.end;
                        let availableDate = new Date(startDate);

                        // Add "Available" dates for all days that aren't reserved
                        while (availableDate < endDate) {
                            let availableDateStr = availableDate.toISOString().split('T')[0];
                            const dayOfWeek = availableDate.getDay();

                            // If the date is not reserved, mark it as "Available"
                            if (dayOfWeek >= 0 && dayOfWeek <= 7 && !reservationDates.has(availableDateStr)) {
                                availableEvents.push({
                                    title: 'Available',
                                    start: availableDateStr,
                                    color: '#20c997' // Color for "Available"
                                });
                            }
                            availableDate.setDate(availableDate.getDate() + 1);
                        }

                        // Combine reservation events and available events
                        successCallback([...events, ...availableEvents]);
                    },
                    dateClick: function(info) {
                        // Prevent clicking on "reserved" dates
                        const clickedDate = info.dateStr;

                        // Check if the clicked date is reserved
                        const isReserved = events.some(event => event.start === clickedDate && event.reserved);

                        if (isReserved) {
                            alert("This date is already reserved. Please choose another date.");
                            return; // Do not allow selection of reserved dates
                        }

                        // When a date is clicked, update the hidden input field
                        const selectedDate = info.dateStr;
                        document.getElementById('selectedReservationDate').value = selectedDate;
                        closeCalendarModal(rentalId); // Close the modal after selecting a date
                    },
                    validRange: {
                        start: moment().add(1, 'days').format('YYYY-MM-DD'),
                        end: moment().add(3, 'months').format('YYYY-MM-DD')
                    },
                    businessHours: {
                        daysOfWeek: [1, 2, 3, 4, 5, 6, 7],
                        startTime: '00:00',
                        endTime: '24:00'
                    },
                    selectConstraint: {
                        daysOfWeek: [1, 2, 3, 4, 5, 6, 7] // Allow selections for all days
                    }
                });

                calendar.render();
            })
            .catch(error => console.error('Error fetching reservations:', error));
    }
}


        function closeCalendarModal(rentalId) {
            document.getElementById('calendarModal-' + rentalId).style.display = 'none';
        }


       
    </script>
@endpush
