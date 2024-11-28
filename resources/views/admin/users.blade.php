@extends('layouts.admin')
@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Users</h3>
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
                    <div class="text-tiny">Users</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
            <div class="wg-filter flex-grow">
            <form class="form-search">
                    <fieldset class="name">
                        <input type="text" id="user-search" placeholder="Search here..." class="" name="name" tabindex="2" required="">
                    </fieldset>
                    <div class="button-submit">
                        <button type="submit" disabled><i class="icon-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="filter-dropdowns flex items-center gap10">
                <!-- Year Level Dropdown -->
                <select name="year_level" id="year_level" class="">
                    <option value="">Year Level</option>
                    <option value="1st Year" {{ request('year_level') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                    <option value="2nd Year" {{ request('year_level') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                    <option value="3rd Year" {{ request('year_level') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                    <option value="4th Year" {{ request('year_level') == '4th Year' ? 'selected' : '' }}>4th Year</option>
                    <!-- Add more year levels -->
                </select>

                <!-- Colleges Dropdown -->
                <select name="department" id="department" class="">
                    <option value="">Select College</option>
                    <option value="CEIT" {{ request('department') == 'CEIT' ? 'selected' : '' }}>CEIT</option>
                    <option value="GSOLC" {{ request('department') == 'GSOLC' ? 'selected' : '' }}>GSOLC</option>
                    <option value="CAFENR" {{ request('department') == 'CAFENR' ? 'selected' : '' }}>CAFENR</option>
                    <option value="CAS" {{ request('department') == 'CAS' ? 'selected' : '' }}>CAS</option>
                    <option value="CCJ" {{ request('department') == 'CCJ' ? 'selected' : '' }}>CCJ</option>
                    <option value="CEMDS" {{ request('department') == 'CEMDS' ? 'selected' : '' }}>CEMDS</option>
                    <option value="CED" {{ request('department') == 'CED' ? 'selected' : '' }}>CED</option>
                    <option value="CON" {{ request('department') == 'CON' ? 'selected' : '' }}>CON</option>
                    <option value="CVMBS" {{ request('department') == 'GSOLC' ? 'selected' : '' }}>CVMBS</option>
                    <!-- Add more colleges -->
                </select>

            </div>

            <ul id="search-results" class="list-group mt-2"></ul>
            <div>
                <a class="tf-button style-1 w208" href="{{route('admin.users.add')}}"><i class="icon-plus"></i>Add new</a>
            </div>
            <div class="table-responsive">
                @if (Session::has('status'))
                    <p class="alert alert-success">{{ Session::get('status') }}</p>
                @endif
                <table class="table table-striped table-bordered" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Phone</th>
                            <th class="text-center">Year Level</th>
                            <th class="text-center">Colleges</th>
                            <th class="text-center">Programs</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td class="text-center">{{ $user->id }}</td>
                            <td class="text-center">{{ $user->name }}</td>
                            <td class="text-center">{{ $user->email }}</td>
                            <td class="text-center">{{ $user->phone_number ?? 'Not Provided' }}</td>
                            <td class="text-center">{{ $user->year_level ?? 'Not Provided' }}</td>
                            <td class="text-center">{{ $user->department ?? 'Not Provided' }}</td>
                            <td class="text-center">{{ $user->course ?? 'Not Provided' }}</td>
                            <td class="text-center">
                                @if ($user->isDefault)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm" title="Edit User">
                                    <i class="icon-edit-3"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm delete" title="Delete User">
                                        <i class="icon-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        $('.delete').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this user?",
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

});
    $(document).ready(function(){
        $('#user-search').on('input', function() {
            let query = $(this).val();

            if (query.length > 0) {
                $.ajax({
                    url: "{{ route('admin.users.search') }}",
                    type: "GET",
                    data: { query: query },
                    success: function(data) {
                        let tableBody = $('tbody');
                        tableBody.empty(); // Clear existing table rows

                        if (data.length > 0) {
                            data.forEach(function(user) {
                                tableBody.append(`
                                    @foreach ($users as $user)
                        <tr>
                            <td class="text-center">{{ $user->id }}</td>
                            <td class="text-center">{{ $user->name }}</td>
                            <td class="text-center">{{ $user->email }}</td>
                            <td class="text-center">{{ $user->phone_number ?? 'Not Provided' }}</td>
                            <td class="text-center">{{ $user->year_level ?? 'Not Provided' }}</td>
                            <td class="text-center">{{ $user->department ?? 'Not Provided' }}</td>
                            <td class="text-center">{{ $user->course ?? 'Not Provided' }}</td>
                            <td class="text-center">
                                @if ($user->isDefault)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm" title="Edit User">
                                    <i class="icon-edit-3"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm delete" title="Delete User">
                                        <i class="icon-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                                `);
                            });
                        } else {
                            tableBody.append(`
                                <tr>
                                    <td colspan="9" class="text-center">No user exists with the name "${query}".</td>
                                </tr>
                            `);
                        }
                    }
                });
            } else {
                location.reload(); // Reload the page if the search is cleared to show all users
            }
        });
    });

        // Filter users when dropdowns change
        $('#year_level, #department').on('change', function() {
        filterUsers();
    });
        function filterUsers() {
            let yearLevel = $('#year_level').val();
            let department = $('#department').val(); // Use 'department' here to match the input field
    
            $.ajax({
                url: "{{ route('admin.users.filter') }}", // Adjust this route accordingly
                type: "GET",
                data: {
                    year_level: yearLevel,
                    department: department // Use 'department' here in the AJAX request data
                },
                success: function(data) {
                    let tableBody = $('tbody');
                    tableBody.empty(); // Clear existing table rows
    
                    if (data.length > 0) {
                        data.forEach(function(user) {
                            tableBody.append(`
                                <tr>
                                    <td class="text-center">${user.id}</td>
                                    <td class="text-center">${user.name}</td>
                                    <td class="text-center">${user.email}</td>
                                    <td class="text-center">${user.phone_number ?? 'Not Provided'}</td>
                                    <td class="text-center">${user.year_level ?? 'Not Provided'}</td>
                                    <td class="text-center">${user.department ?? 'Not Provided'}</td>
                                    <td class="text-center">${user.course ?? 'Not Provided'}</td>
                                    <td class="text-center">
                                        ${user.isDefault ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm" title="Edit User">
                                    <i class="icon-edit-3"></i>
                                </a>
                                        <form action="/admin/users/destroy/${user.id}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm delete" title="Delete User">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tableBody.append(`
                            <tr>
                                <td colspan="9" class="text-center">No users found with the selected filters.</td>
                            </tr>
                        `);
                    }
                },
                error: function() {
                    alert('An error occurred while filtering users.');
                }
            });
        }
</script>
@endpush
