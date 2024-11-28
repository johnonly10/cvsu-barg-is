@extends('layouts.admin')
@section('content')
    <style>
        .main-content-wrap, .form-edit-user, .breadcrumbs, .form-input, .form-select, .button {
            font-family: 'Arial', sans-serif; 
            font-size: 16px; 
            color: #333; 
        }

        .body-title {
            font-weight: bold;
            margin-bottom: 8px;
        }


        .btn-xl {
            padding: 15px 25px;
            font-size: 18px;
            border-radius: 10px;
        }

        .btn-xl:hover {
            background-color: #000000; 
            color: #fff; 
        }

        
        .text-red-600 {
            color: #e3342f;
        }

        
        .mb-4 {
            margin-bottom: 16px;
        }

        .form-edit-user {
            max-width: 70%; 
            margin: 0 auto; 
            padding: 20px; 
        }

        .form-input, .form-select {
            padding: 8px 12px;
        }
    </style>

    <!-- main-content-wrap -->
    <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap-20 mb-27">
                <h3 class="font-bold">Edit User</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap-10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div>Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <a href="{{ route('admin.users') }}">
                            <div>Users</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div>Edit User</div>
                    </li>
                </ul>
            </div>

            <!-- form-edit-user -->
            <form class="form-edit-user bg-white" method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $user->id }}" />

                <div class="wg-box">
                    <fieldset class="mb-4">
                        <div class="body-title">User ID</div>
                        <input class="form-input mb-4 border rounded w-full p-2" type="text" value="{{ $user->id }}" readonly>
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Name</div>
                        <input class="form-input mb-4 border rounded w-full p-2" type="text" value="{{ $user->name }}" readonly>
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Email <span class="text-red-600">*</span></div>
                        <input class="form-input mb-4 border rounded w-full p-2" type="email" name="email" value="{{ $user->email }}" readonly>
                        @error('email') <span class="text-red-600">{{ $message }}</span> @enderror
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Phone Number</div>
                        <input class="form-input mb-4 border rounded w-full p-2" type="text" name="phone_number" value="{{ $user->phone_number }}">
                        @error('phone_number') <span class="text-red-600">{{ $message }}</span> @enderror
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Year Level</div>
                        <select class="form-select mb-4 border rounded w-full p-2" name="year_level">
                            <option value="" disabled>Select Year Level</option>
                            @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year)
                                <option value="{{ $year }}" {{ old('year_level', $user->year_level) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                        @error('year_level') <span class="text-red-600">{{ $message }}</span> @enderror
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Department</div>
                        <select class="form-select mb-4 border rounded w-full p-2" name="department" id="studentDepartment">
                            <option value="" disabled>Select Department</option>
                            @foreach (['CEIT', 'GSOLC', 'CAFENR', 'CAS', 'CCJ', 'CEMDS', 'CED', 'CON', 'CVMBS'] as $dept)
                                <option value="{{ $dept }}" {{ old('department', $user->department) == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                        @error('department') <span class="text-red-600">{{ $message }}</span> @enderror
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Course</div>
                        <select class="form-select mb-4 border rounded w-full p-2" name="course" id="course">
                            <option value="" disabled>Select Course</option>
                        </select>
                        @error('course') <span class="text-red-600">{{ $message }}</span> @enderror
                    </fieldset>

                    <div class="cols gap-10">
                        <button type="submit" class="btn btn-dark btn-lg btn-xl">Update User</button>
                    </div>
                </div>
            </form>
            <!-- /form-edit-user -->
        </div>
        <!-- /main-content-wrap -->
    </div>
    <!-- /main-content-wrap -->
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentDepartmentSelect = document.getElementById('studentDepartment');
        const courseSelect = document.getElementById('course');

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
    });
</script>
@endpush
