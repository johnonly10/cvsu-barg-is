@extends('layouts.app')
@section('content')
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
#reservationDate{
    background-color: var(--bs-green);
    border: none;
}
#reservationDate:hover{
    background-color: var(--bs-teal)!important;
}
</style>

@php
    $user = auth()->user();
    $currentRoute = request()->route()->getName();

    // Determine the base home route based on user type
    $homeRoute = match ($user->utype ?? 'guest') {
        'USR' => route('user.index'),
        'DIR' => route('director.index'),
        'ADM' => route('admin.index'),
        default => route('home.index'),
    };

    // Initialize breadcrumbs array with the Home link
    $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

    // Handle different pages
    if ($currentRoute === 'rentals.checkout') {
        $breadcrumbs[] = ['url' => null, 'label' => 'Checkout Page'];
    } else {
        $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
    }
@endphp

<x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}" :breadcrumbs="$breadcrumbs" />

<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-8 col-lg-6">
        <h2 class="text-center mb-4">Reservation Details</h2>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Error Message -->
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form name="checkout-form" method="POST" action="{{ route('rentals.reserve.events', ['rentalId' => $rental->id]) }}" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="pool_quantity" value="{{ $pool_quantity }}" />
            <input type="hidden" name="internal_quantity" value="{{ $internal_quantity }}" />
            <input type="hidden" name="external_quantity" value="{{ $external_quantity }}" />
            <input type="hidden" name="total_price" id="calculated-total-price" value="{{ $total_price }}" />
            <input type="hidden" name="usage_type" value="{{ $usage_type }}">



            @if (in_array($rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
                <div class="mb-4">
                    <div class="my-account__coursedept-list">
                        <div class="my-account__coursedept-list-item">
                            <div class="my-account__coursedept-list__detail">
                                <div class="mb-3">
                                    <p><strong>Name:</strong> {{ $user->name }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    <p><strong>Phone Number:</strong> {{ $user->phone_number }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Additional details for other roles if necessary -->
                <div class="mb-4">
                    <div class="my-account__coursedept-list">
                        <div class="my-account__coursedept-list-item">
                            <div class="my-account__coursedept-list__detail">
                                <div class="mb-3">
                                    <p><strong>Full Name:</strong> {{ $user->name }}</p>
                                    <p><strong>Phone Number:</strong> {{ $user->phone_number }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    <p><strong>Rental Name:</strong> {{ $rental->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <input type="hidden" name="time_slot" value="7:00 am - 6:00 pm"> 

            <div class="mb-3">
                <h5>Requirements</h5>
                @if($rental->requirements)
                    @php
                        $isImageReq = in_array(strtolower(pathinfo($rental->requirements, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
                    @endphp

                    @if(!$isImageReq)
                        {{-- <p>Download the Requirements document:</p> --}}
                        <p>
                            <a href="{{ asset('uploads/rentals/files/' . $rental->requirements) }}" download>
                                Download Requirements
                            </a>
                        </p>
                    @else
                        {{-- <p>The Requirements is an image:</p> --}}
                        <p>
                            <a href="{{ asset('uploads/rentals/' . $rental->requirements) }}" download>
                                Download Requirements Image
                            </a>
                        </p>
                    @endif
                @else
                    <p>No Requirements document uploaded.</p>
                @endif
                @error('requirements')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="qualification">Qualification Document (PDF/DOC)</label>
                <input type="file" id="qualification" name="qualification" class="form-control" accept=".pdf,.doc,.docx" required>
                @error('qualification')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Conditional Fields for Start and End Dates or Reservation Date -->
            @if (in_array($rental->name, ['Male Dormitory', 'Female Dormitory']))
            <div class="mb-3">
                <label><strong>Start Date:</strong></label>
                <p>{{ $dormitoryRoom->start_date }}</p>
            </div>
            <div class="mb-3">
                <label><strong>End Date:</strong></label>
                <p>{{ $dormitoryRoom->end_date }}</p>
            </div>
            
            @elseif ($rental->name == 'International House II' )
                <div class="mb-3">
                    <label for="ih_start_date"><strong>Start Date:</strong></label>
                    <input type="text" id="ih_start_date" name="ih_start_date" class="form-control" placeholder="Select start date" readonly>
                    @error('ih_start_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="ih_end_date"><strong>End Date:</strong></label>
                    <input type="text" id="ih_end_date" name="ih_end_date" class="form-control" placeholder="Select end date" readonly>
                    @error('ih_end_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="calculated_price"><strong>Total Price:</strong></label>
                    <input type="text" id="calculated_price" name="calculated_price" class="form-control" readonly value="0">
                </div>
    
            @else
            <div class="mb-3">
                 <div id="selectedDateDisplay" style="margin-top: 10px; color: black; font-weight: 500;"></div>
                <button id="reservationDate" name="reservation_date" type="button" class="btn text-white mt-3" onclick="openCalendarModal({{ $rental->id }})">
                    Pick a Date to Reserve 
                </button>
                <input type="hidden" id="selectedReservationDate" name="reservation_date" value="{{ old('reservation_date') }}">
                
                
                <div id="calendarModal-{{ $rental->id }}" class="calendar-modal" style="display: none;">
                    <div class="calendar-content">
                        <button type="button" class="close-btn" onclick="closeCalendarModal({{ $rental->id }})">
                            <span><i class="fa fa-xmark"></i></span>
                        </button>
                        <div id="calendar-{{ $rental->id }}" class="rental-calendar"></div>
                    </div>
                </div>
                @error('reservation_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>


            @endif
        




            <button type="submit" class="btn btn-warning w-100">Place Reservation</button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>

function openCalendarModal(rentalId) {
    document.getElementById('calendarModal-' + rentalId).style.display = 'block';

    // Check if this specific calendar is already initialized
    const calendarEl = document.getElementById('calendar-' + rentalId);
    if (!calendarEl.classList.contains('fc')) {
        fetch(`/api/rentals/${rentalId}/reservations?timestamp=${new Date().getTime()}`)
            .then(response => response.json())
            .then(reservations => {
                let events = [];
                const reservationDates = new Set();

                reservations.forEach(item => {
                    let eventColor, eventTitle;

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
                        color: eventColor
                    });

                    reservationDates.add(item.reservation_date);
                });

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: function(fetchInfo, successCallback, failureCallback) {
                        let availableEvents = [...events];
                        const startDate = fetchInfo.start;
                        const endDate = fetchInfo.end;
                        let currentDate = new Date(startDate);

                        while (currentDate < endDate) {
                            const dateStr = currentDate.toISOString().split('T')[0];
                            const dayOfWeek = currentDate.getDay();

                            if (!reservationDates.has(dateStr) && dayOfWeek >= 0 && dayOfWeek <= 7) {
                                availableEvents.push({
                                    title: 'Available',
                                    start: dateStr,
                                    color: '#20c997'
                                });
                            }
                            currentDate.setDate(currentDate.getDate() + 1);
                        }

                        successCallback(availableEvents);
                    },
                    dateClick: function(info) {
                        const selectedDate = info.dateStr;
                        document.getElementById('selectedReservationDate').value = selectedDate;
                        document.getElementById('selectedDateDisplay').innerHTML = `Selected Date: <span style="color: green;">${selectedDate}</span>`;
                        closeCalendarModal(rentalId);
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
                        daysOfWeek: [1, 2, 3, 4, 5, 6, 7]
                    }
                });

                calendar.render();
            })
            .catch(error => console.error('Error fetching reservations:', error));
    }
}


// Close the calendar modal
function closeCalendarModal(rentalId) {
    document.getElementById('calendarModal-' + rentalId).style.display = 'none';
}

// Close the modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('calendarModal-{{ $rental->id }}');
    if (event.target === modal) {
        closeCalendarModal({{ $rental->id }});
    }
}


document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('ih_start_date');
    const endDateInput = document.getElementById('ih_end_date');
    const calculatedPriceInput = document.getElementById('calculated_price');
    const dailyPrice = {{ $rental->price ?? 0 }}; // Assuming rental price is passed from the backend

    flatpickr(startDateInput, {
        dateFormat: 'Y-m-d',
        minDate: new Date().fp_incr(1), // Tomorrow
        onChange: function (selectedDates) {
            const startDate = selectedDates[0];
            // Reset the end date when the start date changes
            endDateInput.value = '';
            flatpickr(endDateInput, {
                dateFormat: 'Y-m-d',
                minDate: startDate, // Set the minimum end date to the selected start date
                onChange: function (selectedEndDates) {
                    const endDate = selectedEndDates[0];
                    if (startDate && endDate) {
                        const timeDiff = Math.abs(endDate - startDate);
                        const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                        const totalPrice = days * dailyPrice;
                        calculatedPriceInput.value = totalPrice.toFixed(2);
                        document.getElementById('calculated-total-price').value = totalPrice.toFixed(2); // Update hidden total price input
                    }
                },
            });
        },
    });
});




</script>
@endpush
