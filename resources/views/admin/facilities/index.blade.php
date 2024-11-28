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
            <h3>Facilities</h3>
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
                    <div class="text-tiny">Facilities</div>
                </li>
            </ul>
        </div>
        
        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                
                <a class="tf-button style-1 w208" href="{{ route('admin.facilities.create') }}"><i class="icon-plus"></i>Add New</a>
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
                            <th>Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($facilities as $facility)
                        <tr>
                            <td>{{ $facility->id }}</td>
                            <td class="pname">
                                <div class="image">
                                    @if($facility->image && File::exists(public_path('uploads/facilitys/thumbnails/' . $facility->image)))
                                        <img src="{{ asset('uploads/facilitys/thumbnails/' . $facility->image) }}" alt="{{ $facility->name }}" class="image">
                                    @else
                                        <img src="{{ asset('images/upload/upload-1.png') }}" alt="No Image" class="image">
                                    @endif
                                </div>
                                <div class="name">
                                    <a href="#" class="body-title-2 facility-name" data-id="{{ $facility->id }}">{{ $facility->name }}</a>
                                    <div class="tooltip" id="tooltip-{{ $facility->id }}">
                                        <strong>Rules:</strong>
                                        <span class="badge {{ $facility->rules_and_regulations ? 'badge-success' : 'badge-danger' }}">
                                            {{ $facility->rules_and_regulations ? 'Available' : 'N/A' }}
                                        </span>
                                        <br>
                                        <strong>Requirements:</strong>
                                        <span class="badge {{ $facility->requirements ? 'badge-success' : 'badge-danger' }}">
                                            {{ $facility->requirements ? 'Available' : 'N/A' }}
                                        </span>
                                    </div>
                                    <span class="badge {{ ucfirst($facility->status) === 'Available' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($facility->status) }}
                                    </span>
                                </div>
                            </td>
                            {{-- <td> 
                            {{-- <td>{{ $facility->capacity ? $facility->capacity : 'N/A'}}</td>
                            @if(in_array($facility->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
                                    {{ $facility->total_room_capacity ? $facility->total_room_capacity : 'N/A' }}
                                @else
                                    {{ $facility->capacity ? $facility->capacity : 'N/A' }}
                                @endif
                                
                            </td> --}}
                            {{-- <td>{{ $facility->price ?  $facility->price : 'N/A' }}</td> --}}
                            <td>{{ $facility->type ? $facility->type : 'N/A' }}</td>
                            <td>{{ $facility->description ? $facility->description : 'N/A' }}</td>
                            <td>{{ $facility->status ? $facility->status : 'N/A' }}</td>
                            <td>{{ $facility->featured ? $facility->featured : 'N/A' }}</td>
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
                            

                            {{-- @if (in_array($facility->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
                            @if($facility->dormitoryRooms->isEmpty())
                                <td colspan="2" class="text-center">No rooms available.</td>
                            @else
                                <td>
                                    @foreach($facility->dormitoryRooms as $room)
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
                            @endif --}}
                                    
                            <td>
                                <div class="list-icon-function">
                                    <a href="#">
                                        <div class="item edit">
                                            <i class="icon-edit-3"></i>
                                        </div>
                                    </a>
                                    <form action="#" method="POST" style="display:inline;">
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
                            <td colspan="11" class="text-center">No facilities found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Code -->
            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $facilities->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
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
        $('.facility-name').hover(function() {
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
