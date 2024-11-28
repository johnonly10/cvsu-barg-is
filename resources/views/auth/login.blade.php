@extends('layouts.app')
@section('content')
<style>
    
.divider-wrapper {
    display: flex;
    flex-direction: row;
    text-transform: uppercase;
    border: none;
    font-size: 12px;
    font-weight: 400;
    margin: 10px 0 0;
    padding: 0;
    align-items: center;
    justify-content: center;
    width: 100%; 
}

.divider-wrapper::before,
.divider-wrapper::after {
    content: "";
    border-bottom: 1px solid #c2c8d0;
    flex: 1 0 auto;
    height: 0.5em;
    margin: 0;
}

.divider {
    text-align: center;    
    flex: 0 1 auto;
    margin: 20px 16px;
    height: 12px;
}


</style>
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <div class="container" style="padding-top: 100px;">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-0">
                        <div class="card-header border-0 text-center fw-bold">
                            <span class="title-form">{{ __('Login') }}</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('login') }}" name="login-form" class="needs-validation"
                                novalidate id="login-form">
                                @csrf

                                <!-- Email Input -->
                                <div class="form-floating mb-3">
                                    <input id="email" type="email"
                                        class="form-control form-control_gray @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                        placeholder=" ">
                                    <label for="email">Email address</label>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Continue Button -->
                                <button type="button"
                                    class="btn btn-btn-dark form-control bg-dark text-white mb-3 w-100 text-uppercase"
                                    id="continue-btn">Continue</button>

                                <!-- Password Input Container -->
                                <div id="password-container" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <input id="password" type="password"
                                            class="form-control form-control_gray @error('password') is-invalid @enderror"
                                            name="password" required autocomplete="current-password" placeholder=" ">
                                        <label for="password">Password</label>
                                        <button type="button" class="btn-password-toggle"
                                            onclick="togglePasswordVisibility()" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Show password">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <!-- Remember Me Checkbox -->
                                        <div class="form-check mb-3">
                                            <input type="checkbox" class="form-check-input custom-checkbox" id="rememberMe" name="remember">
                                            <label class="form-check-label" for="rememberMe">{{ __('Remember Me') }}</label>
                                        </div>


                                        <!-- Forgot Password Link -->
                                        <div>
                                            <a href="{{ route('password.request') }}" class="forgot-password-link">
                                                {{ __('Forgot Your Password?') }}
                                            </a>
                                        </div>
                                    </div>


                                    <!-- Submit Button -->
                                    <button
                                        class="btn btn-btn-dark form-control bg-dark text-white mb-1 w-100 text-uppercase"
                                        type="submit">{{ __('Login') }}</button>
                                </div>


                                <div class="divider-wrapper"><span class="divider">Or</span></div>

                                <!-- Google Sign-In Button -->
                                <div class="text-center">
                                    <a href="{{ url('auth/google') }}"
                                        class="btn btn-light btn-google w-100 text-uppercase">
                                        <img src="{{ asset('./images/googleicon.svg') }}" alt="Google Icon"
                                            class="google-icon">
                                        Sign in with Google
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

<script>
    function togglePasswordVisibility() {
        const passwordField = document.getElementById('password');
        const toggleButton = document.querySelector('.btn-password-toggle');
        const toggleIcon = toggleButton.querySelector('i');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleButton.setAttribute('title', 'Hide password');
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleButton.setAttribute('title', 'Show password');
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
        // Reinitialize tooltip to update the title
        const tooltip = bootstrap.Tooltip.getInstance(toggleButton);
        tooltip.dispose();
        new bootstrap.Tooltip(toggleButton);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        const emailInput = document.getElementById('email');
        const passwordContainer = document.getElementById('password-container');
        const continueBtn = document.getElementById('continue-btn');

        continueBtn.addEventListener('click', function() {
            const email = emailInput.value;
            if (email.endsWith('@cvsu.edu.ph') || email.endsWith('@gmail.com')) {
                passwordContainer.style.display = 'block';
                continueBtn.style.display = 'none';
            } else {
                alert('Please use your cvsu or Gmail email address.');
            }
        });

        // Handle form submission
        document.getElementById('login-form').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            if (!password) {
                event.preventDefault();
                alert('Please enter your password.');
            }
        });
    });
</script>
