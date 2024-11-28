<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminProfileController extends Controller
{
    

    public function show_profile() 
    {

       $user = Auth::user();
        return view('admin.profile', compact('user'));
    }


    

    
}
