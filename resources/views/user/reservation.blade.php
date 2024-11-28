@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $currentRoute = request()->route()->getName();

    // Determine the base home route based on user type
    $homeRoute = match ($user->utype ?? 'guest') {
        'USR' => route('user.index'),
        'ADM' => route('admin.index'),
        default => route('home.index'),
    };

    $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

    // Customize breadcrumbs based on the current route
    $breadcrumbs[] = [
        'url' => null, 
        'label' => $currentRoute === 'user.rentals' ? 'Rentals' : ucwords(str_replace('.', ' ', $currentRoute))
    ];
@endphp

<x-header 
    backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" 
    title="{{ last($breadcrumbs)['label'] }}"
    :breadcrumbs="$breadcrumbs" 
/>

<main class="container" style="padding-top: 1em;">
    <section class="my-account container">
        <h2 class="page-title">Rentals</h2>

        <div class="row">
            <div class="col-lg-2">
                @include('user.account__nav')
            </div>

            <div class="col-lg-10">
                <div class="accordion mt-5" id="rentalAccordion">
                    @foreach ($reservations as $reservation)
                        <div class="accordion-item mb-3 shadow-sm border rounded">
                            <h2 class="accordion-header" id="headingReservation{{ $reservation->id }}">
                                <button class="accordion-button d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReservation{{ $reservation->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapseReservation{{ $reservation->id }}">
                                    <span>
                                        <strong>Reservation #{{ $reservation->id }}</strong>
                                        <small class="text-muted">Placed on {{ $reservation->created_at->format('M d, Y') }}</small>
                                    </span>
                                    <span>
                                        <span class="badge bg-{{ $reservation->rent_status == 'completed' ? 'success' : ($reservation->rent_status == 'canceled' ? 'danger' : ($reservation->rent_status == 'reserved' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($reservation->rent_status) }}
                                        </span>
                                    </span>
                                </button>
                            </h2>
                            <div id="collapseReservation{{ $reservation->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="headingReservation{{ $reservation->id }}" data-bs-parent="#rentalAccordion">
                                <div class="accordion-body">
                                    <!-- Reservation Summary Section -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <div>
                                            <p><strong>Name: </strong>{{ $reservation->rental->name }}</p>
                                            <p><strong>Status: </strong>{{ ucfirst($reservation->rent_status) }}</p>
                                            <p><strong>Payment: </strong>{{ ucfirst($reservation->payment_status) }}</p>
                                        </div>
                                        <div>
                                            <p><strong>Total Price: </strong>
                                                @if ($reservation->rental->name === 'International House II')
                                                    {{-- Calculate total price for IH2 based on reservation_ih2_date --}}
                                                    &#8369; {{ number_format($reservation->total_price, 2) }}
                                                @elseif ($reservation->rental->name === 'Swimming Pool')
                                                    &#8369; {{ number_format($reservation->total_price, 2) }}
                                                @elseif (in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory']) && $reservation->dormitoryRoom)
                                                    &#8369; {{ number_format($reservation->rental->price, 2) }}
                                                @else
                                                    &#8369; {{ number_format($reservation->total_price, 2) }}
                                                @endif
                                            </p>
                                            
                                            @if ($reservation->rental->name === 'Swimming Pool')
                                                <p><strong>Pool Quantity: </strong>{{ $reservation->pool_quantity }}</p>
                                            @elseif (in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']) && $reservation->rent_status === 'reserved' && $reservation->dormitoryRoom)
                                                <p><strong>Room Number: </strong> {{ $reservation->dormitoryRoom->room_number }}</p>
                                                <p><strong>Room Capacity: </strong> {{ $reservation->dormitoryRoom->room_capacity }}</p>
                                            @endif
                                        </div>
                                    </div>
                                
                                    <!-- Reservation Footer Details -->
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <p class="text-muted"><strong>Reservation Date:</strong> 
                                            @if ($reservation->rental->name === 'International House II' && $reservation->reservation_ih2_date)
                                                {{-- Show all reserved dates for International House II --}}
                                                {{ implode(', ', json_decode($reservation->reservation_ih2_date, true)) }}
                                            @elseif ($reservation->dormitoryRoom && in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory']))
                                                {{ $reservation->dormitoryRoom->start_date }}
                                            @else
                                                {{ $reservation->reservation_date }}
                                            @endif
                                        </p>
                                        <a href="{{ route('user.reservation-details', ['reservation_id' => $reservation->id]) }}" class="btn btn-outline-primary">View Details</a>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="divider"></div>

                <div class="d-flex justify-content-between flex-wrap gap-2 pagination-wrapper">
                    {{ $reservations->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>
</main>
@endsection


@push('styles')
<style>
    .accordion-button {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pagination-wrapper {
        margin-top: 20px;
    }
</style>
@endpush
