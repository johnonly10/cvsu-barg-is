@extends('layouts.admin')

@section('content')

<style>
    /* Ensure the modal backdrop covers the entire screen */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1040; /* Make sure it is below the modal */
        background-color: rgba(0, 0, 0, 0.7); /* Adjust opacity as needed */
    }

    /* Ensure the modal content is centered */
    .modal-dialog {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }

    .modal-content {
        width: 100%;
        max-width: 800px; /* Adjust max-width as needed */
    }
</style>

<div class="main-content-inner">                             
    <div class="main-content-wrap">
        <!-- Page header with breadcrumbs -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h3>All Messages</h3>
            <ul class="breadcrumbs d-flex flex-wrap gap-2">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-muted">Dashboard</div>
                    </a>
                </li>                                                                           
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-muted">All Messages</div></li>
            </ul>
        </div>
        
        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap mb-4">
                <div class="wg-filter flex-grow">
                    <form class="form-search">
                        <fieldset class="name">
                            <input type="text" placeholder="Search messages..." class="form-control" name="name"
                                tabindex="2" value="" aria-required="true" required=""/>
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Success message -->
            @if(Session::has('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ Session::get('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Card-based layout for messages -->
            <div class="row g-4">
                @foreach ($contacts as $contact)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div>
                                        <h5 class="card-title mb-0">{{ $contact->name }}</h5>
                                        <small class="text-muted">{{ $contact->email }}</small>
                                    </div>
                                </div>
                                <p class="card-text"><strong>Phone:</strong> {{ $contact->phone }}</p>
                                <p class="card-text">
                                    <strong>Message:</strong> 
                                    <span class="message-preview">{{ Str::limit($contact->message, 100) }}</span>
                                    @if(strlen($contact->message) > 100)
                                        <a href="#" class="text-primary view-full-message" data-bs-toggle="modal" data-bs-target="#messageModal{{ $contact->id }}">...</a>
                                    @endif
                                </p>
                                <p class="card-text"><small class="text-muted">Received on {{ $contact->created_at->format('M d, Y at H:i') }}</small></p>
                            </div>
                            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#messageModal{{ $contact->id }}">
                                    <i class="icon-eye"></i> View Details
                                </button>
                                <form action="{{ route('admin.contact.delete', ['id' => $contact->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-sm delete">
                                        <i class="icon-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal for full message ad reply -->
                    <div class="modal fade" id="messageModal{{ $contact->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="messageModalLabel{{ $contact->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="messageModalLabel{{ $contact->id }}">Message from {{ $contact->name }}</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Contact Details -->
                                    <p><strong>Email:</strong> {{ $contact->email }}</p>
                                    <p><strong>Phone:</strong> {{ $contact->phone }}</p>
                                    <p><strong>Sent on:</strong> {{ $contact->created_at->format('M d, Y at H:i') }}</p>
                                    <hr>
                                    <p><strong>Message:</strong></p>
                                    <p>{{ $contact->message }}</p>

                                    <hr>

                                    <!-- Admin's Reply Form -->
                                    <form action="{{ route('admin.contact.reply', ['id' => $contact->id]) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="replyMessage" class="form-label">Your Reply:</label>
                                            <textarea class="form-control" name="replyMessage" id="replyMessage{{ $contact->id }}" rows="4" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Send Reply</button>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $contacts->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 Script -->

<script>
    $(function() {
        $('.delete').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            var button = $(this);
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this message?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                preConfirm: () => {
                    button.prop('disabled', true); // Disable button
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    button.prop('disabled', false); // Re-enable button if cancelled
                }
            });
        });
    });
</script>
@endpush
