<style>
    .nav-links {
    color: #333;
    font-weight: bold;
    padding: 10px;
    border-radius: 8px;
    transition: background-color 0.3s, color 0.3s;
    }

    .nav-links:hover {
        background-color: #f8f9fa;
    }
</style>

<div class="col-md-3 mb-4">
    <div class="profile-sidebar">
        <!-- Profile Image -->
        <div class="profile-image mb-3 d-flex justify-content-center">
            @if(Auth::user()->profile_image)
                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}"
                    alt="Profile Image"
                    class="img-fluid rounded-circle"
                    style="width: 120px; height: 120px; object-fit: cover;">
            @else
                <img src="{{ asset('images/profile.jpg') }}" alt="Default Profile Image"
                    class="img-fluid rounded-circle"
                    style="width: 120px; height: 120px; object-fit: cover;">
            @endif
        </div>
        <p>{{ Auth::user()->name }}</p>
        <a href="{{route('user.profile.image.edit')}}" class="text-muted  btn btn-yellow btn-sm small mb-3 d-block"><i class="fas fa-edit"></i> Edit Profile Image</a>
        <form action="{{ route('user.profile.image.delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your profile image?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-muted small mb-3 btn btn-red btn-sm"><i class="fas fa-trash"></i> Delete Profile Image</button>
        </form>
        <hr>
        <nav class="nav flex-column mt-4">
            <a class="nav-links" href="{{ route('user.profile') }}">
                <i class="fas fa-user"></i> Profile
            </a>
            <a class="nav-links" href="{{ route('password.request') }}">
                <i class="fas fa-lock"></i> Change Password
            </a>
        </nav>
    </div>
</div>
