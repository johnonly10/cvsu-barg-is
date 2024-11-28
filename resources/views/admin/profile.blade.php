@extends('layouts.admin')

@section('content')
    <style>
        /* Styling for the Admin Profile Page */
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
        }

        h2 {
            font-size: 28px;
            font-weight: bold;
            color: #343a40;
        }

        p.text-muted {
            font-size: 16px;
            color: #6c757d;
        }

        hr {
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }

        .form-label {
            font-weight: bold;
            color: #495057;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-shadow: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        }

        .img-thumbnail {
            border-radius: 50%;
            border: 2px solid #dee2e6;
            object-fit: cover;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            border-radius: 5px;
            font-size: 14px;
        }

        .text-end button {
            margin-top: 20px;
        }

        .form-control[disabled] {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }
    </style>

    <div class="container mt-5">
        <h2>Admin Profile</h2>
        <p class="text-muted">Update your profile information</p>
        <hr>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="#" method="POST" enctype="multipart/form-data">
            {{-- @csrf --}}

            <!-- Profile Image -->
            <div class="mb-3 text-center">
                <label for="profile_image" class="form-label">Profile Image</label>
                <div class="d-flex flex-column align-items-center">
                    <input type="file" name="profile_image" id="profile_image" class="form-control mb-2"
                        style="width: 300px;">
                    @if ($user->profile_image)
                        <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile Image"
                            class="img-thumbnail mt-2" style="width: 150px; height: 150px;">
                    @else
                        <img src="{{ asset('images/default-profile.png') }}" alt="Default Profile Image"
                            class="img-thumbnail mt-2" style="width: 150px; height: 150px;">
                    @endif
                </div>
            </div>

            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" disabled value="{{ $user->name }}"
                    required>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" disabled value="{{ $user->email }}"
                    required>
            </div>

            <a class="nav-links" href="{{ route('password.request') }}">
                <i class="fas fa-lock"></i> Change Password
            </a>
            <!-- Save Button -->
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
