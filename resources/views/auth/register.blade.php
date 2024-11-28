@extends('layouts.app')

@section('content')

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <div class="container" style="padding-top: 100px;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0">
                    <div class="card-header border-0 text-center fw-bold"> <span class="title-form">{{ __('Register') }}</span> </div>
    
                    <div class="card-body">
                        <form method="POST" action="{{ route('register') }}" name="register-form" class="needs-validation" novalidate="">
                            @csrf
    
                            <div class="form-floating mb-3">
                                <input id="name" type="text" class="form-control form-control_gray @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder=" ">
                                <label for="name">Name</label>
    
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
    
                            <div class="pb-3"></div>
    
                            <div class="form-floating mb-3">
                                <input id="email" type="email" class="form-control form-control_gray @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder=" ">
                                <label for="email">Email address</label>
    
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
    
                            <div class="pb-3"></div>
    
                            <div class="form-floating mb-3 position-relative">
                                <input id="password" type="password" class="form-control form-control_gray @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder=" ">
                                <label for="password">Password</label>
                                <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('password', this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
    
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
    
                            <div class="pb-3"></div>
                            
                            <div class="form-floating mb-3 position-relative">
                                <input id="password-confirm" type="password" class="form-control form-control_gray" name="password_confirmation" required autocomplete="new-password" placeholder=" ">
                                <label for="password-confirm">Confirm Password</label>
                                <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('password-confirm', this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            

                            <div class="d-flex align-items-center mb-3 pb-2">
                                <p class="m-0 text-secondary fs-6">Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our privacy policy.</p>
                              </div>
    
                            <button class="btn btn-btn-dark  form-control bg-dark text-white mb-1 w-100 text-uppercase" type="submit">{{ __('Register') }}</button>
    
                            <div class="customer-option mt-4 text-center">
                                <span class="text-secondary">Already have an account?</span>
                                <a href="{{ route('login') }}" class="btn-text">Sign In</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function togglePasswordVisibility(fieldId, button) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = button.querySelector('i');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            button.setAttribute('title', 'Hide password');
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            button.setAttribute('title', 'Show password');
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }

        // Reinitialize tooltip to update the title
        const tooltip = bootstrap.Tooltip.getInstance(button);
        tooltip.dispose();
        new bootstrap.Tooltip(button);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

@endsection
