@extends('layouts.app')
@section('content')

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
    if ($currentRoute === 'shop.index') {
        $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
    } elseif ($currentRoute === 'cart.checkout') {
        $breadcrumbs[] = ['url' => null, 'label' => 'Checkout Page'];
    } else {
        $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
    }
@endphp

<x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}" :breadcrumbs="$breadcrumbs" />
<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-8 col-lg-6">
        <h2 class="text-center mb-4">Reservation Details</h2>
        <form name="checkout-form" action="{{ route('cart.place.an.order') }}" method="POST">
            @csrf

            @if ($user->role === 'student')
                <div class="mb-4">
                    <div class="my-account__coursedept-list">
                        <div class="my-account__coursedept-list-item">
                            <div class="my-account__coursedept-list__detail">
                                <div class="mb-3">
                                    <p><strong>Full Name:</strong> {{ $user->name }}</p>
                                    <p><strong>Phone Number:</strong> {{ $user->phone_number }}</p>
                                    <p><strong>Year Level:</strong> {{ $user->year_level }}</p>
                                    <p><strong>Department:</strong> {{ $user->department }}</p>
                                    <p><strong>Course:</strong> {{ $user->course }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($user->role === 'employee' || $user->role === 'non-employee')
            <div class="mb-4">
                <div class="my-account__coursedept-list">
                    <div class="my-account__coursedept-list-item">
                        <div class="my-account__coursedept-list__detail">
                            <div class="mb-3">
                                <p><strong>Full Name:</strong> {{ $user->name }}</p>
                                <p><strong>Phone Number:</strong> {{ $user->phone_number }}</p>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="mb-3">
                <label for="reservationDate">Reservation Date</label>
                <input type="text" id="reservationDate" name="reservation_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="timeSlot">Time Slot</label>
                <div id="timeSlotContainer" class="form-select">
                </div>
                @error('time_slot')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="order-summary mb-4">
                <h3 class="text-center">YOUR ORDER</h3>
                <table class="table checkout-cart-item">
                    <thead>
                        <tr>
                            <th>PRODUCT</th>
                            <th class="text-end">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (Cart::instance('cart')->content() as $item)
                            <tr>
                                <td>{{ $item->name }} x {{ $item->qty }}</td>
                                <td class="text-end">{{ $item->price}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><strong>SUBTOTAL</strong></td>
                            <td class="text-end"><strong>{{ Cart::instance('cart')->subtotal() }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>TOTAL</strong></td>
                            <td class="text-end"><strong>{{ Cart::instance('cart')->total() }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-warning w-100">Place Reservation</button>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department');
    const courseSelect = document.getElementById('course');
    const reservationDateInput = document.getElementById('reservationDate');
    const timeSlotContainer = document.getElementById('timeSlotContainer');
    const phoneInput = document.getElementById('phoneNumber');
    const submitButton = document.querySelector('button[type="submit"]');

        const courses = {
            CEIT: ['BS Agricultural and Biosystems Engineering', 'BS Architecture', 'BS Civil Engineering', 'BS Computer Engineering', 'BS Computer Science', 'BS Electrical Engineering', 'BS Electronics Engineering', 'BS Industrial Technology Major in Automotive Technology', 'BS Industrial Technology Major in Electrical Technology', 'BS Industrial Technology Major in Electronics Technology', 'BS Information Technology'],
            GSOLC: ['PhD in Agriculture', 'PhD in Education', 'PhD in Management', 'Master in Business Administration', 'Master in Agriculture', 'Master of Arts in Education', 'Master in Engineering', 'Master of Management', 'Master of Professional Studies', 'MS Agriculture', 'MS Biology', 'MS Food Science', 'Master in Information Technology'],
            CAFENR: ['Bachelor of Agricultural Entrepreneurship', 'BS Agriculture', 'BS Environmental Science', 'BS Food Technology'],
            CAS: ['BA English Language Studies', 'BA Journalism', 'BA Political Science', 'BA Applied Mathematics', 'BS Biology', 'BS Psychology', 'BS Social Work'],
            CCJ: ['BS Criminology', 'BS Industrial Security Management'],
            CEMDS: ['BS Accountancy', 'BS Business Management', 'BS Economics', 'BS International Studies', 'BS Office Administration'],
            CED: ['Bachelor of Early Childhood Education', 'Bachelor of Elementary Education', 'Bachelor of Secondary Education', 'Bachelor of Special Needs Education', 'Bachelor of Technology and Livelihood Education', 'BS Hospitality Management', 'BS Tourism Management', 'Teacher Certificate Program', 'Science High School', 'Elementary Education', 'Pre-Elementary Education'],
            CON: ['BS Medical Technology', 'BS Midwifery', 'BS Nursing', 'Diploma in Midwifery'],
            CVMBS: ['Doctor of Veterinary Medicine']
        };

        function updateCourseOptions() {
            const department = departmentSelect.value;
            courseSelect.innerHTML = '<option value="" disabled selected>Select Course</option>';
            if (courses[department]) {
                courses[department].forEach(function(course) {
                    const option = document.createElement('option');
                    option.value = course;
                    option.textContent = course;
                    courseSelect.appendChild(option);
                });
            }
        }

        if (departmentSelect) {
            departmentSelect.addEventListener('change', updateCourseOptions);
        }

        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
                if (this.value.length > 0 && this.value[0] !== '9') {
                    this.value = '9' + this.value.slice(1);
                }
            });
        }

    let unavailableDates = JSON.parse(localStorage.getItem('unavailableDates')) || [];

    async function fetchUnavailableDates() {
        if (unavailableDates.length === 0) {
            const response = await fetch('/api/get-unavailable-dates');
            const data = await response.json();
            unavailableDates = data.map(date => moment(date).toDate());
            localStorage.setItem('unavailableDates', JSON.stringify(unavailableDates));
        }
    }

    function isDateAvailable(date) {
        const dayOfWeek = date.getDay();
        return dayOfWeek !== 0 && !unavailableDates.some(d => moment(d).isSame(date, 'day'));
    }

    fetchUnavailableDates();

    const picker = new Pikaday({
        field: reservationDateInput,
        format: 'YYYY-MM-DD',
        minDate: moment().toDate(),
        maxDate: moment().add(1, 'months').toDate(),
        onSelect: function(date) {
            updateTimeSlots(moment(date).format('YYYY-MM-DD'));
        },
        disableDayFn: function(date) {
            return !isDateAvailable(date);
        },
        render: function(date, cellNode) {
            if (isDateAvailable(date)) {
                cellNode.style.backgroundColor = 'lightgreen';
                cellNode.style.color = 'black';
            } else {
                cellNode.style.backgroundColor = 'lightcoral';
                cellNode.style.color = 'white';
            }
        }
    });

    function updateTimeSlots(selectedDate) {
        fetch(`/api/get-time-slots?date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                timeSlotContainer.innerHTML = '';
                const dateData = data.find(item => item.date === selectedDate);
                if (dateData && dateData.timeSlots) {
                    dateData.timeSlots.forEach(slot => {
                        const radioDiv = document.createElement('div');
                        radioDiv.className = 'form-check';

                        const radioInput = document.createElement('input');
                        radioInput.type = 'radio';
                        radioInput.name = 'time_slot';
                        radioInput.id = `timeSlot_${slot.time_slot}`;
                        radioInput.value = slot.time_slot;
                        radioInput.className = 'form-check-input';
                        radioInput.required = true;
                        radioInput.disabled = slot.slots_left === 0;

                        const radioLabel = document.createElement('label');
                        radioLabel.className = 'form-check-label';
                        radioLabel.htmlFor = radioInput.id;
                        radioLabel.textContent = `${slot.time_slot} - ${slot.slots_left} slots left`;
                        radioLabel.style.color = slot.slots_left > 0 ? 'green' : 'red';

                        radioDiv.appendChild(radioInput);
                        radioDiv.appendChild(radioLabel);
                        timeSlotContainer.appendChild(radioDiv);
                    });
                } else {
                    console.error('No time slots available for the selected date');
                }
            })
            .catch(error => console.error('Error fetching time slots:', error));
    }

    // Submission handling
    submitButton.addEventListener('click', function(e) {
        const selectedTimeSlot = document.querySelector('input[name="time_slot"]:checked');
        if (!selectedTimeSlot) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please select a time slot before placing the order!',
            });
        }
    });
    });
</script>
@endpush



