<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

 
 
    public function handleGoogleCallback()
    {
        try {
            $socialiteUser = Socialite::driver('google')->stateless()->user();

            // Get the user's email
            $email = $socialiteUser->getEmail();

            // Check if the email is from @cvsu.edu.ph or @gmail.com
            if (!str_ends_with($email, '@cvsu.edu.ph') && !str_ends_with($email, '@gmail.com')) {
                return redirect()->route('login')->withErrors('Please use your @cvsu.edu.ph or @gmail.com email address.');
            }

            // Find or create a user
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $socialiteUser->getName(), 'password' => Hash::make(uniqid())] // Set a random password only for new users
            );

            // If the user already exists, do not overwrite the password
            if (!$user->wasRecentlyCreated && $user->password_set) {
                // User exists and has set a password, just log them in
                Auth::login($user);
                return redirect()->intended('/');
            }

            // For new users or users who haven't set a password, log them in and prompt to set a password
            Auth::login($user);

            // Redirect to password set page if they haven't set one
            if (!$user->password_set) {
                return redirect()->route('password.set');
            }

            return redirect()->intended('/');
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors('Login failed, please try again.');
        }
    }
}
