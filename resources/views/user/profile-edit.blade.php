@extends('layouts.app')
@php
    $user = auth()->user();
    $currentRoute = request()->route()->getName();

    // Determine the base home route based on user type
    $homeRoute = match ($user->utype ?? 'guest') {
        'USR' => route('user.index'),
        'ADM' => route('admin.index'),
        default => route('home.index'),
    };

    // Initialize breadcrumbs array with the Home link
    $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

    // Handle Shop pages
    if ($currentRoute === 'shop.index') {
        $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
    } elseif ($currentRoute === 'shop.product.details') {
        $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
        $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];

    // Handle About page
    } elseif ($currentRoute === 'about.index') {
        $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];

    // Handle Contact page
    } elseif ($currentRoute === 'contact.index') {
        $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];

    // Add more pages as needed
    } else {
        $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
    }
@endphp

<x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
    :breadcrumbs="$breadcrumbs" />

    <style>
        /* General styles */
        .profile-sidebar {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
    
        .profile-image img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    
        .profile-content {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    
        .profile-content h2 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }
    
        .profile-content p {
            font-size: 16px;
            color: #555;
        }
    
        .alert {
            margin-top: 20px;
        }
    
        /* Button styles */
        .btn-black {
            background-color: #343a40;
            color: #fff;
        }
    
        .btn-black:hover {
            background-color: #23272b;
        }
    </style>

@section('content')
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-12 mt-5">
                <div class="row h-100">
                    <x-profile-sidebar />

                    <div class="col-md-9 mb-4">
                        <div class="bg-white p-4 border rounded shadow-sm profile-content h-100">
                            <h2>Update Profile</h2>
                            <p>Edit your personal information</p>
                            <hr>
                            <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="fullName" name="name" placeholder="Enter Full Name" disabled value="{{ old('name', $user->name) }}">
                                        <label for="fullName">Full Name</label>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Address" disabled value="{{ old('email', $user->email) }}">
                                        <label for="email">Email</label>
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <div class="input-group">
                                            <span class="input-group-text">+63</span>
                                            <input type="tel" class="form-control" id="phoneNumber" name="phone_number" placeholder="Phone Number" value="{{ old('phone_number', $user->phone_number) }}" pattern="^9\d{9}$" maxlength="10">
                                        </div>
                                    </div>
                                    @error('phone_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="sex" name="sex" required>
                                            <option value="" disabled>Select Sex</option>
                                            <option value="male" {{ old('sex', $user->sex) === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('sex', $user->sex) === 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        <label for="sex" class="form-label">Sex</label>
                                    </div>
                                    @error('sex')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="role" name="role" {{ auth()->user()->role_change_allowed ? 'required' : 'disabled' }}>
                                            <option value="" disabled>Select Role</option>
                                            <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                                            <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>Employee</option>
                                            <option value="non-employee" {{ old('role', $user->role) == 'non-employee' ? 'selected' : '' }}>Non-Employee</option>
                                        </select>
                                        <label for="role">Role</label>
                                    </div>
                                    @error('role')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Student Fields -->
                                <div id="studentFields" style="display: none;">
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="yearLevel" name="year_level">
                                                <option value="" disabled {{ old('year_level', $user->year_level) ? '' : 'selected' }}>Select Year Level</option>
                                                @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year)
                                                    <option value="{{ $year }}" {{ old('year_level', $user->year_level) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                                @endforeach
                                            </select>
                                            <label for="yearLevel">Year Level</label>
                                            @error('year_level')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="studentDepartment" name="department">
                                                <option value="" disabled {{ old('department', $user->department) ? '' : 'selected' }}>Select Department</option>
                                                @foreach (['CEIT', 'GSOLC', 'CAFENR', 'CAS', 'CCJ', 'CEMDS', 'CED', 'CON', 'CVMBS'] as $dept)
                                                    <option value="{{ $dept }}" {{ old('department', $user->department) == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                                @endforeach
                                            </select>
                                            <label for="studentDepartment">Department</label>
                                            @error('department')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="course" name="course">
                                                <option value="" disabled selected>Select Course</option>
                                            </select>
                                            <label for="course">Course</label>
                                            @error('course')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div id="othersFields" style="display: none;">
                                    <!-- Additional fields for non-employees can be added here -->
                                </div>

                                <div class="mt-4 mb-3">
                                    <button type="submit" class="btn btn-black" id="updateProfileBtn">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const studentFields = document.getElementById('studentFields');
        const professorFields = document.getElementById('professorFields');
        const othersFields = document.getElementById('othersFields');
        const currentRole = '{{ old('role', $user->role) }}';

        const studentDepartmentSelect = document.getElementById('studentDepartment');
        const courseSelect = document.getElementById('course');
        const phoneInput = document.getElementById('phoneNumber');

        // Courses based on department
        const courses = {
            CEIT: ['BS Agricultural and Biosystems Engineering', 'BS Architecture', 'BS Civil Engineering',
                'BS Computer Engineering', 'BS Computer Science', 'BS Electrical Engineering',
                'BS Electronics Engineering', 'BS Industrial Technology Major in Automotive Technology',
                'BS Industrial Technology Major in Electrical Technology',
                'BS Industrial Technology Major in Electronics Technology', 'BS Information Technology'
            ],
            GSOLC: ['PhD in Agriculture', 'PhD in Education', 'PhD in Management',
                'Master in Business Administration', 'Master in Agriculture',
                'Master of Arts in Education', 'Master in Engineering', 'Master of Management',
                'Master of Professional Studies', 'MS Agriculture', 'MS Biology', 'MS Food Science',
                'Master in Information Technology'
            ],
            CAFENR: ['Bachelor of Agricultural Entrepreneurship', 'BS Agriculture',
                'BS Environmental Science', 'BS Food Technology'
            ],
            CAS: ['BA English Language Studies', 'BA Journalism', 'BA Political Science',
                'BA Applied Mathematics', 'BS Biology', 'BS Psychology', 'BS Social Work'
            ],
            CCJ: ['BS Criminology', 'BS Industrial Security Management'],
            CEMDS: ['BS Accountancy', 'BS Business Management', 'BS Economics', 'BS International Studies',
                'BS Office Administration'
            ],
            CED: ['Bachelor of Early Childhood Education', 'Bachelor of Elementary Education',
                'Bachelor of Secondary Education', 'Bachelor of Special Needs Education',
                'Bachelor of Technology and Livelihood Education', 'BS Hospitality Management',
                'BS Tourism Management', 'Teacher Certificate Program', 'Science High School',
                'Elementary Education', 'Pre-Elementary Education'
            ],
            CON: ['BS Medical Technology', 'BS Midwifery', 'BS Nursing', 'Diploma in Midwifery'],
            CVMBS: ['Doctor of Veterinary Medicine']
        };
        
        // Phone number input validation
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
                if (this.value.length > 0 && this.value[0] !== '9') {
                    this.value = '9' + this.value.slice(1);
                }
            });
        }
        // Update courses dropdown
        function updateCourseOptions() {
            const department = studentDepartmentSelect.value;
            courseSelect.innerHTML = '<option value="" disabled selected>Select Course</option>';

            if (courses[department]) {
                courses[department].forEach(function(course) {
                    const option = document.createElement('option');
                    option.value = course;
                    option.textContent = course;
                    courseSelect.appendChild(option);
                });

                const oldValue = '{{ old('course', $user->course) }}';
                if (oldValue) {
                    courseSelect.value = oldValue;
                }
            }
        }
         // Call updateCourseOptions when the department is changed
        studentDepartmentSelect.addEventListener('change', updateCourseOptions);

        // Call it initially to populate the courses if there's an old value
        updateCourseOptions();
        function updateFieldsVisibility(role) {
            if (!role) {
                return; // Ensure role is defined
            }

            studentFields.style.display = 'none';
                othersFields.style.display = 'none';

            // Show fields based on role
            if (role === 'student') {
                studentFields.style.display = 'block';
            } else if (role === 'employee' || role === 'non-employee') {
                othersFields.style.display = 'block'; // Optional fields for non-employees can be handled here
            }
    }

        // Initialize visibility on page load (with correct role)
        updateFieldsVisibility(currentRole);

        // Listen for role changes to toggle fields dynamically
        roleSelect.addEventListener('change', function() {
            updateFieldsVisibility(this.value);
        });

        // Confirmation dialog for deleting records
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
        });
    });
</script>
@endpush





