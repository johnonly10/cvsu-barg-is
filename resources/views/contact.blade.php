@extends('layouts.app')
@section('content')
<style>
   .text-danger {
      color: #e72010 !important;
   }
   .btn-black:disabled {
      cursor: not-allowed;
      background: #888 !important;
   }
</style>

@php
$user = auth()->user();
$currentRoute = request()->route()->getName();

// Determine the base home route based on user type
$homeRoute = match ($user->utype ?? 'guest') {
    'USR' => route('user.index'),
    'DIR' => route('director.index'),
    'ADM' => route('admin.index'),
    default => route('home.index'),
};

// Initialize breadcrumbs array with the Home link
$breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

// Handle different routes and their breadcrumbs
if ($currentRoute === 'shop.index') {
    $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
} elseif ($currentRoute === 'shop.product.details') {
    $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
    $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];
} elseif ($currentRoute === 'about.index') {
    $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];
} elseif ($currentRoute === 'home.contact') {
    $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];
} else {
    $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
}
@endphp

<x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
:breadcrumbs="$breadcrumbs" />

<main class="container my-5">

  <!-- Contact Us Form section -->
  <section class="contact-us">
    <div class="row justify-content-center">
      <div class="col-md-8">

        <!-- Display errors related to user information -->
        @if ($errors->has('user_info'))
          <div class="alert alert-danger">
            {{ $errors->first('user_info') }}
          </div>
        @endif

        <!-- Flash messages for success or error -->
        @if (Session::has('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ Session::get('success') }}
          </div>
        @endif

        @if (Session::has('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ Session::get('error') }}
          </div>
        @endif

        <!-- Form -->
        <form name="contact-us-form" class="needs-validation" novalidate action="{{ route('home.contact.store') }}" method="POST">
          @csrf
          <h3 class="mb-5 text-center">Get In Touch</h3>
          
          <!-- Name input -->
          <div class="form-floating mb-4">
            <input type="text" class="form-control" id="contact_us_name" name="name" placeholder="Your Full Name" 
                   value="{{ old('name', $user->name ?? '') }}" 
                   readonly required>
            <label for="contact_us_name">Name *</label>
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
          </div>

          <!-- Phone input -->
          <div class="form-floating mb-4">
            <input type="text" class="form-control" id="contact_us_phone" name="phone" placeholder="Your Phone Number" 
                   value="{{ old('phone', $user->phone_number ?? '') }}" 
                   {{ $user ? 'readonly' : '' }} required>
            <label for="contact_us_phone">Phone *</label>
            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
          </div>

          <!-- Email input -->
          <div class="form-floating mb-4">
            <input type="email" class="form-control" id="contact_us_email" name="email" placeholder="Your Email Address" 
                   value="{{ old('email', $user->email ?? '') }}" 
                   {{ $user ? 'readonly' : '' }} required>
            <label for="contact_us_email">Email address *</label>
            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
          </div>

          <!-- Message textarea -->
          <div class="form-floating mb-4">
            <textarea class="form-control" name="message" id="contact_us_message" placeholder="Write your message here" 
                      style="height: 150px;" required>{{ old('message') }}</textarea>
            <label for="contact_us_message">Your Message *</label>
            @error('message') <span class="text-danger">{{ $message }}</span> @enderror
          </div>

          <!-- Submit button -->
          <div class="d-grid">
            <button type="submit" id="submit_button" class="btn btn-black btn-lg">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>

<script>
  // SweetAlert2 for success messages
  @if (Session::has('success'))
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: '{{ Session::get('success') }}',
      confirmButtonText: 'Okay'
    });
  @endif

  // SweetAlert2 for error messages when a user exceeds the time window
  @if (Session::has('error'))
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: '{{ Session::get('error') }}',
      confirmButtonText: 'Try Again'
    });
  @endif

  // SweetAlert2 for unauthenticated users
  @if ($errors->has('no_account'))
    Swal.fire({
      icon: 'warning',
      title: 'You Don\'t Have an Account!',
      text: '{{ $errors->first('no_account') }}',
      showCancelButton: false,
      confirmButtonText: 'Login'
    }).then(() => {
      // Redirect to the login page
      window.location.href = '{{ route('login') }}';
    });
  @endif

  // Validation errors handling for missing user info
  @if ($errors->has('user_info'))
    Swal.fire({
      icon: 'warning',
      title: 'Incomplete Profile!',
      text: '{{ $errors->first('user_info') }}',
      showCancelButton: true,
      confirmButtonText: 'Fill Up',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        // Redirect the user to the account edit page
        window.location.href = '{{ route('user.profile.edit', ['id' => auth()->user()->id]) }}';
      }
    });
  @endif

  // Validation errors handling for form fields
  @if ($errors->any() && !$errors->has('user_info') && !$errors->has('no_account'))
    Swal.fire({
      icon: 'error',
      title: 'Validation Error!',
      text: 'Please fill in all required fields.',
      confirmButtonText: 'Okay'
    }).then(() => {
      let firstInvalidField = document.querySelector('.is-invalid');
      if (firstInvalidField) {
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalidField.focus();
      }
    });
  @endif
</script>

@endsection
