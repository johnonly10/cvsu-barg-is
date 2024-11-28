@extends('layouts.app')

@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <div class="container" style="padding-top: 100px;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0">
                    <div class="card-header border-0 text-center fw-bold">
                        <span class="title-form">{{ __('Set Your Password') }}</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.set') }}" class="needs-validation" novalidate>
                            @csrf

                            <!-- New Password Field -->
                            <div class="form-floating mb-3">
                                <input id="password" type="password" class="form-control form-control_gray @error('password') is-invalid @enderror" name="password" placeholder=" " required>
                                <label for="password">New Password</label>
                                <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('password', this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <!-- Confirm Password Field -->
                            <div class="form-floating mb-3">
                                <input id="password_confirmation" type="password" class="form-control form-control_gray @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder=" " required>
                                <label for="password_confirmation">Confirm Password</label>
                                <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('password_confirmation', this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @error('password_confirmation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <button
                            class="btn btn-btn-dark form-control bg-dark text-white mb-1 w-100 text-uppercase"
                            type="submit">{{ __('Set Password') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function togglePasswordVisibility(fieldId, toggleButton) {
        const passwordField = document.getElementById(fieldId);
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

        const tooltip = bootstrap.Tooltip.getInstance(toggleButton);
        tooltip.dispose();
        new bootstrap.Tooltip(toggleButton);
    }
</script>
@endsection
