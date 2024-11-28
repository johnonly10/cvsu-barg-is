@extends('layouts.admin')
@section('content')
     <!-- main-content-wrap -->
     <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Add User</h3>
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
                        <a href="{{ route('admin.users') }}">
                            <div class="text-tiny">Users</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Add User</div>
                    </li>
                </ul>
            </div>

            <!-- form-add-user -->
            <form class="form-add-user" method="POST" enctype="multipart/form-data" action="{{ route('admin.users.store') }}">
               @csrf

                <div class="wg-box">
                    <fieldset class="name">
                        <div class="body-title mb-10">Name <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Enter user name" name="name" required="">
                    </fieldset>
                    @error('name') <span class="alert alert-danger text-center">{{ $message }} </span> @enderror

                    <fieldset class="email">
                        <div class="body-title mb-10">Email <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="email" placeholder="Enter user email" name="email" required="">
                    </fieldset>
                    @error('email') <span class="alert alert-danger text-center">{{ $message }} </span> @enderror

                    <fieldset class="role">
                        <div class="body-title mb-10">Role <span class="tf-color-1">*</span></div>
                        <div class="select">
                            <select name="role" id="roleSelect" required>
                                <option value="student">Student</option>
                                <option value="professor">Professor</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('role') <span class="alert alert-danger text-center">{{ $message }} </span> @enderror

                    <!-- Year Level -->
                    <fieldset class="year_level">
                        <div class="body-title mb-10">Year Level</div>
                        <div class="select">
                            <select name="year_level" id="yearLevel">
                                <option value="" disabled selected>Select Year Level</option>
                                @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('year_level') <span class="alert alert-danger text-center">{{ $message }} </span> @enderror

                    <!-- Department -->
                    <fieldset class="department">
                        <div class="body-title mb-10">Department</div>
                        <div class="select">
                            <select name="department" id="department">
                                <option value="" disabled selected>Select Department</option>
                                @foreach (['CEIT', 'GSOLC', 'CAFENR', 'CAS', 'CCJ', 'CEMDS', 'CED', 'CON', 'CVMBS'] as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('department') <span class="alert alert-danger text-center">{{ $message }} </span> @enderror

                    <!-- Course -->
                    <fieldset class="course">
                        <div class="body-title mb-10">Course</div>
                        <div class="select">
                            <select name="course" id="course">
                                <option value="" disabled selected>Select Course</option>
                                <!-- Course options will be populated dynamically based on department -->
                            </select>
                        </div>
                    </fieldset>
                    @error('course') <span class="alert alert-danger text-center">{{ $message }} </span> @enderror

                    <fieldset class="phone_number">
                        <div class="body-title mb-10">Phone Number</div>
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <input type="tel" class="form-control" id="phoneNumber" name="phone_number" placeholder="Phone Number" pattern="^9\d{9}$" maxlength="10">
                        </div>
                    </fieldset>
                    @error('phone_number') <span class="alert alert-danger text-center">{{ $message }} </span> @enderror
                    <div class="bot">
                        <div></div>
                        <button class="tf-button w208" type="submit">Save</button>
                    </div>
                </div>

            </form>
            <!-- /form-add-user -->
        </div>
        <!-- /main-content-wrap -->
    </div>
    <!-- /main-content-wrap -->
@endsection

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.querySelector('input[name="email"]');
        const form = document.querySelector('form.form-add-user');
        const profileImageInput = document.getElementById('profileImage');
        const departmentSelect = document.getElementById('department');
        const courseSelect = document.getElementById('course');
        const roleSelect = document.getElementById('roleSelect');
        const yearLevelSelect = document.getElementById('yearLevel');
    
        // Email Validation for "@cvsu.edu.ph" Domain
        form.addEventListener('submit', function(e) {
            const emailValue = emailInput.value;
            const emailDomain = '@cvsu.edu.ph';
            if (!emailValue.endsWith(emailDomain)) {
                e.preventDefault();
                alert('The email must be a @cvsu.edu.ph email.');
            }
        });
    
    
        // Populate Courses Based on Selected Department
        const courses = {
            CEIT: ['BS Agricultural and Biosystems Engineering', 'BS Architecture', 'BS Civil Engineering', 'BS Computer Science', 'BS Information Technology'],
            GSOLC: ['PhD in Agriculture', 'PhD in Management', 'Master of Arts in Education', 'MS Agriculture'],
            CAFENR: ['BS Agriculture', 'BS Environmental Science', 'BS Food Technology'],
            CAS: ['BA Political Science', 'BS Psychology', 'BS Biology'],
            CCJ: ['BS Criminology'],
            CEMDS: ['BS Accountancy', 'BS Business Management'],
            CED: ['Bachelor of Elementary Education', 'Bachelor of Secondary Education'],
            CON: ['BS Nursing'],
            CVMBS: ['Doctor of Veterinary Medicine']
        };
    
        if (departmentSelect && courseSelect) {
            departmentSelect.addEventListener('change', function() {
                const selectedDepartment = this.value;
                courseSelect.innerHTML = '<option value="" disabled selected>Select Course</option>';
                if (courses[selectedDepartment]) {
                    courses[selectedDepartment].forEach(function(course) {
                        const option = document.createElement('option');
                        option.value = course;
                        option.textContent = course;
                        courseSelect.appendChild(option);
                    });
                }
            });
        }
    
        // Disable Year Level and Course for Professors and Others
        roleSelect.addEventListener('change', function() {
            if (this.value === 'professor' || this.value === 'other') {
                yearLevelSelect.value = 'N/A';
                yearLevelSelect.disabled = true;
                courseSelect.value = 'N/A';
                courseSelect.disabled = true;
            } else {
                yearLevelSelect.disabled = false;
                courseSelect.disabled = false;
            }
        });
    });
    </script>









@endpush
