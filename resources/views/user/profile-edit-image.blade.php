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

// Handle various pages
if ($currentRoute === 'shop.index') {
    $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
} elseif ($currentRoute === 'shop.product.details') {
    $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
    $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];
} elseif ($currentRoute === 'about.index') {
    $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];
} elseif ($currentRoute === 'contact.index') {
    $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];
} else {
    $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
}
@endphp

<x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}" :breadcrumbs="$breadcrumbs" />
<style>
    .upload-image {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }

    .upload-image .item {
        margin: 10px 0;
        position: relative;
        width: 100%;
    }

    #preview-img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #ddd;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    #upload-file {
        text-align: center;
        width: 100%;
    }

    .uploadfile {
        display: block;
        padding: 20px;
        background-color: #f8f9fa;
        border: 2px dashed #ddd;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s, border-color 0.3s;
        text-align: center;
    }

    .uploadfile:hover {
        background-color: #e9ecef;
        border-color: #ccc;
    }

    .uploadfile .icon {
        font-size: 32px;
        color: #007bff;
        margin-bottom: 10px;
    }

    .uploadfile .body-text {
        font-size: 18px;
        color: #333;
    }

    .uploadfile .tf-color {
        color: #007bff;
        font-weight: bold;
        font-size: 13px;
    }

    /* Profile Sidebar */
    .profile-sidebar {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .profile-sidebar .profile-image img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #ddd;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .profile-sidebar p {
        font-weight: bold;
        font-size: 18px;
        margin-top: 15px;
        color: #333;
    }

    .profile-content h2 {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .profile-content p {
        font-size: 16px;
        color: #555;
    }
</style>
@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-12 mt-5">
            <div class="row h-100">
                <x-profile-sidebar />

                 <!-- Profile Content -->
                 <div class="col-md-9 mb-4">
                    <div class="bg-white p-4 border rounded shadow-sm profile-content h-100">
                        <h2>Update Profile</h2>
                        <p>Edit your personal information and profile image</p>
                        <hr>

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Form to update profile image -->
                        <form action="{{ route('user.profile.image.update') }}" method="POST" 
                              enctype="multipart/form-data" id="profileImageForm">
                            @csrf
                            @method('PUT')

                            <fieldset>
                                <div class="upload-image flex-grow">
                                    <div class="item" id="imgpreview" style="display:none; text-align: center;">
                                        <img id="preview-img"
                                            class="img-thumbnail img-fluid rounded-circle mx-auto d-block"
                                            style="width: 200px; height: 200px; object-fit: cover;" 
                                            src="{{ $user->profile_image ? Storage::url($user->profile_image) : asset('images/default-avatar.png') }}"
                                            alt="Profile Image">
                                    </div>
                                    <div id="upload-file" class="item up-load">
                                        <label class="uploadfile" for="myFile">
                                            <span class="icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </span>
                                            <span class="body-text" style="font-size: 13px">
                                                Upload your image here
                                                <span class="tf-color">click here to browse</span>
                                            </span>
                                            <input type="file" id="myFile" name="profile_image" 
                                                   accept="image/png,image/jpeg,image/jpg" 
                                                   style="opacity: 0; height: 1px; display:none;">
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-black">Update Profile Image</button>
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
    $(document).ready(function() {
           // Show preview if image exists
    if ($('#preview-img').attr('src') !== '{{ asset("images/default-avatar.png") }}') {
        $('#imgpreview').show();
    }
        $("#myFile").on("change", function() {
            const [file] = this.files;
            if (file) {
                const imgPreview = $("#imgpreview");
                const previewImg = $("#preview-img");
                imgPreview.show();
                previewImg.attr('src', URL.createObjectURL(file));
            }
        });
    });

</script>
@endpush
