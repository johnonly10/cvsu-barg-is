<!-- resources/views/admin/reservation.blade.php -->

@extends('layouts.admin')

@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Reservations</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Reservations</div>
                </li>
            </ul>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search" method="GET" action="{{ route('admin.reservation') }}">
                        <fieldset class="name">
                            <input type="text" placeholder="Search here..." class="" name="search"
                                tabindex="2" value="{{ request('search') }}" aria-required="true">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th>Reservation ID</th>
                                <th>User Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Rental Name</th>
                                <th>Qualification</th>
                                <th>Status</th>
                                <th>Reservation Date</th>
                                <th>Available Room</th> <!-- New Column -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reservations as $reservation)
                            <tr>
                                <td class="text-center">{{ $reservation->id }}</td>
                                <td class="text-center">{{ $reservation->user->name }}</td>
                                <td class="text-center">
                                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $reservation->user->email }}" target="_blank">
                                        {{ $reservation->user->email }}
                                    </a>
                                </td>
                                <td class="text-center">{{ $reservation->user->phone_number }}</td>
                                <td class="text-center">{{ $reservation->rental->name }}</td>
                                <td class="text-center">
                                    @if($reservation->rental->qualification)
                                        <a href="{{ asset('uploads/rentals/files/' . $reservation->rental->qualification) }}" download>
                                            {{ $reservation->rental->qualification }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="text-center">{{ ucfirst($reservation->rent_status) }}</td>
                                <td class="text-center">
                                    @if($reservation->rental->name === 'International House II')
                                        @if($reservation->dormitoryRoom)
                                            {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_start_date)->format('Y-m-d') }}
                                            to
                                            {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_end_date)->format('Y-m-d') }}
                                        @else
                                            N/A
                                        @endif
                                    @elseif(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory']) && $reservation->dormitoryRoom)
                                        {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->start_date)->format('Y-m-d') }}
                                        to
                                        {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->end_date)->format('Y-m-d') }}
                                    @else
                                        {{ $reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('Y-m-d') : 'N/A' }}
                                    @endif
                                </td>
                                
                                
                                <td class="text-center">
                                    @if(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
                                        @php
                                            // Fetch available rooms for the rental ID
                                            $availableRoomsForRental = $availableRooms->get($reservation->rental->id) ?? collect();
                                        @endphp
                                
                                        @if($availableRoomsForRental->count() > 0)
                                            <ul style="list-style: none; padding: 0; margin: 0;">
                                                @foreach($availableRoomsForRental as $room)
                                                    <li class="room-number-container" data-room-id="{{ $room->id }}">
                                                        <a href="#" class="room-number-link">
                                                            Room {{ $room->room_number }}
                                                        </a>
                                                        <div class="tooltip" id="room-tooltip-{{ $room->id }}">
                                                            <strong>Room Capacity:</strong> {{ $room->room_capacity }}<br>
                                                            <strong>Currently Reserved:</strong> {{ $room->reservations_count ?? 0 }}
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span>No available rooms</span>
                                        @endif
                                    @else
                                        <span>N/A</span>
                                    @endif
                                </td>
                                
                                
                                
                                <td>
                                    <div class="list-icon-function">
                                        <a href="{{ route('admin.reservation-events', ['reservation_id' => $reservation->id]) }}">
                                            <div class="item edit">
                                                <i class="icon-edit-3"></i>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="list-icon-function">
                                        <a href="{{ route('admin.reservation-history', ['reservation_id' => $reservation->id]) }}">
                                            <div class="item eye">
                                                <i class="icon-eye"></i>
                                            </div>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $reservations->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 20px;
        text-align: left;
    }

    .table td {
        white-space: nowrap;
    }

    /* Styling the Available Room list */
    .table ul {
        list-style-type: none;
        padding-left: 0;
        margin: 0;
    }

    .table li {
        margin-bottom: 5px;
    }

    .room-number-container {
        position: relative;
        cursor: pointer;
    }

    .room-number-link {
        text-decoration: none;
        font-weight: normal;
    }

    .tooltip {
        display: none;
        position: absolute;
        background-color: #333;
        color: white;
        padding: 8px;
        border-radius: 4px;
        z-index: 9999; /* Ensure the tooltip appears above other elements */
        font-size: 12px;
        max-width: 200px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        opacity: 0;  /* Initially hidden */
        transition: opacity 0.3s ease-in-out;  /* Smooth transition */
    }

    .room-number-container:hover .tooltip {
        display: block;
        opacity: 1; /* Fade in effect */
    }
</style>
@endpush

@push('scripts')
<script>
    $(function() {
        // Tooltip functionality for available rooms
        $(document).on('mouseenter', '.room-number-link', function() {
            var tooltipId = '#room-tooltip-' + $(this).parent().data('room-id');
            $(tooltipId).addClass('show');
        });

        $(document).on('mouseleave', '.room-number-link', function() {
            var tooltipId = '#room-tooltip-' + $(this).parent().data('room-id');
            $(tooltipId).removeClass('show');
        });
    });
</script>


@endpush
