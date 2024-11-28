@extends('layouts.admin')

@section('content')
<style>
    table {
        table-layout: auto;
        width: 100%;
        border-collapse: collapse; /* Collapses borders for a cleaner look */
    }

    th, td {
        padding: 12px 15px; /* Adjusted padding for better spacing */
        border: 1px solid #ddd;
        word-wrap: break-word;
        white-space: normal;
        text-align: left; /* Align text to the left for better readability */
    }

    th {
        background-color: #f8f9fa; /* Light background for headers */
        font-weight: bold; /* Make header text bold */
    }

    th:nth-child(8), td:nth-child(8) {
        min-width: 100px;
    }
    .table-striped th:nth-child(1), .table-striped td:nth-child(1) {
        width: 50px;
    }
    .table-striped th:nth-child(2), .table-striped td:nth-child(2) {
        width: 250px;
    }
    .table-striped th:nth-child(9), .table-striped td:nth-child(9) {
        width: 100px;
    }

    /* Additional Styling for Price Display */
    .price-container {
        display: flex;
        flex-direction: column;
    }
    .price-container span {
        margin-bottom: 4px;
    }

    /* Tooltip Styling */
    .tooltip {
        display: none;
        position: absolute;
        background-color: #333;
        color: #fff;
        padding: 8px;
        border-radius: 4px;
        z-index: 1000;
        font-size: 12px;
        max-width: 200px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); 
    }

    .tooltip.show {
        display: block;
    }

    /* Badge Styles */
    .badge {
        padding: 0.25em 0.6em;
        border-radius: 0.25rem;
        color: #fff;
        font-weight: bold;
        font-size: 0.75em;
        display: inline-block; 
    }
    .badge-success {
        background-color: #28a745; 
    }
    .badge-danger {
        background-color: #dc3545; 
    }

    .list-icon-function .item {
        cursor: pointer; /
    }

    
    tr:hover {
        background-color: #f1f1f1; 
    }
</style>

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Rentals</h3>
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
                    <div class="text-tiny">Rentals</div>
                </li>
            </ul>
        </div>
        
        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                
                <a class="tf-button style-1 w208" href="{{ route('admin.rental.add') }}"><i class="icon-plus"></i>Add New</a>
            </div>
            <div class="table-responsive">
                @if(Session::has('status'))
                    <p class="alert alert-success">{{ Session::get('status') }}</p>
                @endif
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Capacity</th>
                            <th>Price</th>
                            <th>Internal Price</th>
                            <th>External Price</th>
                            <th>Exclusive Price</th>
                            <th>Room Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rentals as $rental)
                        <tr>
                            <td>{{ $rental->id }}</td>
                            <td class="pname">
                                <div class="image">
                                    @if($rental->image && File::exists(public_path('uploads/rentals/thumbnails/' . $rental->image)))
                                        <img src="{{ asset('uploads/rentals/thumbnails/' . $rental->image) }}" alt="{{ $rental->name }}" class="image">
                                    @else
                                        <img src="{{ asset('images/upload/upload-1.png') }}" alt="No Image" class="image">
                                    @endif
                                </div>
                                <div class="name">
                                    <a href="#" class="body-title-2 rental-name" data-id="{{ $rental->id }}">{{ $rental->name }}</a>
                                    <div class="tooltip" id="tooltip-{{ $rental->id }}">
                                        <strong>Rules:</strong>
                                        <span class="badge {{ $rental->rules_and_regulations ? 'badge-success' : 'badge-danger' }}">
                                            {{ $rental->rules_and_regulations ? 'Available' : 'N/A' }}
                                        </span>
                                        <br>
                                        <strong>Requirements:</strong>
                                        <span class="badge {{ $rental->requirements ? 'badge-success' : 'badge-danger' }}">
                                            {{ $rental->requirements ? 'Available' : 'N/A' }}
                                        </span>
                                    </div>
                                    <span class="badge {{ ucfirst($rental->status) === 'Available' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($rental->status) }}
                                    </span>
                                </div>
                            </td>
                            <td> 
                            {{-- <td>{{ $rental->capacity ? $rental->capacity : 'N/A'}}</td> --}}
                            @if(in_array($rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
                                    {{ $rental->total_room_capacity ? $rental->total_room_capacity : 'N/A' }}
                                @else
                                    {{ $rental->capacity ? $rental->capacity : 'N/A' }}
                                @endif
                                
                            </td>
                            <td>{{ $rental->price ?  $rental->price : 'N/A' }}</td>
                            <td>{{ $rental->internal_price ? $rental->internal_price : 'N/A' }}</td>
                            <td>{{ $rental->external_price ? $rental->external_price : 'N/A' }}</td>
                            <td>{{ $rental->exclusive_price ? $rental->exclusive_price : 'N/A' }}</td>
                            {{-- @if($dormitoryRooms->isEmpty())
                                <td colspan="2" class="text-center">No rooms available.</td>
                            @else
                                @foreach($dormitoryRooms as $room)
                                    <td>
                                        <div class="room-number" data-id="{{ $room->id }}">
                                            <a href="#" class="room-number-link">{{ $room->room_number ? $room->room_number : 'N/A'}}</a>
                                            <div class="tooltip" id="room-tooltip-{{ $room->id }}">
                                                <strong>Room Capacity:</strong> {{ $room->room_capacity }}
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                            @endif --}}
                            

                            @if (in_array($rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
                            @if($rental->dormitoryRooms->isEmpty())
                                <td colspan="2" class="text-center">No rooms available.</td>
                            @else
                                <td>
                                    @foreach($rental->dormitoryRooms as $room)
                                        <div class="room-number" data-id="{{ $room->id }}">
                                            <a href="#" class="room-number-link">{{ $room->room_number ? $room->room_number : 'N/A' }}</a>
                                            <div class="tooltip" id="room-tooltip-{{ $room->id }}">
                                                <strong>Room Capacity:</strong> {{ $room->room_capacity }}
                                            </div>
                                        </div>
                                    @endforeach
                                </td>
                            @endif
                            @else
                                <td>N/A</td> 
                            @endif
                                    
                            <td>
                                <div class="list-icon-function">
                                    <a href="{{ route('admin.rental.edit', ['id' => $rental->id]) }}">
                                        <div class="item edit">
                                            <i class="icon-edit-3"></i>
                                        </div>
                                    </a>
                                    <form action="{{ route('admin.rental.delete', ['id' => $rental->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="item text-danger delete">
                                            <i class="icon-trash-2"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">No rentals found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Code -->
            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $rentals->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
            </div>
        </div>
    
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 script for delete confirmation -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        $('.delete').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this record?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Tooltip functionality
        $('.rental-name').hover(function() {
            var tooltipId = '#tooltip-' + $(this).data('id');
            $(tooltipId).addClass('show');
        }, function() {
            var tooltipId = '#tooltip-' + $(this).data('id');
            $(tooltipId).removeClass('show');
        });
        $('.room-number-link').hover(function() {
            var tooltipId = '#room-tooltip-' + $(this).parent().data('id');
            $(tooltipId).addClass('show');
        }, function() {
            var tooltipId = '#room-tooltip-' + $(this).parent().data('id');
            $(tooltipId).removeClass('show');
        });
    });
</script>
@endpush
