<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function showSetPasswordForm()
    {
        return view('auth.passwords.set');
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->password_set = true;
        $user->save();

        return redirect()->route('user.profile')->with([
            'incomplete_profile' => true,
            'message' => 'Your password has been successfully updated. Please complete your profile to proceed.'
        ]);
    }
}
