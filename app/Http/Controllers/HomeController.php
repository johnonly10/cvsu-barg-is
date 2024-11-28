<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Slide;
use App\Models\Rental;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\ContactMessageReceived;
use App\Notifications\ContactMessageNotification;

class HomeController extends Controller
{
    public function index()
    {
        $slides = Slide::where('status', 1)->get()->take(3);
        $categories = Category::orderBy('name')->get();
        $fproducts = Product::where('featured',1)->get()->take(8);
        $frentals = Rental::where('featured',1)->get()->take(8);
        return view('index', compact('slides', 'categories','fproducts','frentals'));  

    }
    public function contact()
    {
        return view ('contact');

    }


    
    public function contact_store(Request $request)
    {
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return redirect()->back()->withErrors([
                'no_account' => 'You need to log in to send a message.'
            ]);
        }

        // Check if user information is incomplete
        if (!$user->name || !$user->email || !$user->phone_number) {
            return redirect()->back()->withErrors([
                'user_info' => 'Your profile information is incomplete. Please update your profile (name, email, and phone number) to send a message.'
            ]);
        }

        // Validate the message field
        $request->validate([
            'message' => 'required|max:65535',
        ], [
            'message.required' => 'The message field is required.',
            'message.max' => 'The message must not exceed 65535 characters.'
        ]);

        // Check if the user has sent a message within the time window
        // $lastContact = Contact::where('user_id', $user->id)
        //                     ->latest()
        //                     ->first();
        // $timeWindow = 60; // Time window in minutes

        // if ($lastContact && Carbon::parse($lastContact->created_at)->diffInMinutes(Carbon::now()) < $timeWindow) {
        //     return redirect()->back()->with('error', 'You can only send one message every ' . $timeWindow . ' minutes.');
        // }

        // Save the contact message
        $contact = new Contact();
        $contact->name = $user->name;      
        $contact->email = $user->email;    
        $contact->phone = $user->phone_number;
        $contact->message = $request->message; 
        $contact->user_id = $user->id;
        $contact->save();

        // Notify the admin about the new message
        $admin = User::where('utype', 'ADM')->first();
        if ($admin) {
            $admin->notify(new ContactMessageNotification($contact));
        }

        // Fire an event for the new contact message
        event(new ContactMessageReceived($contact)); 
        
        return redirect()->back()->with('success', 'Your message has been sent successfully.');
    }






    
    


    public function search(Request  $request)
    {
        $query = $request->input('query');
        $results = Product::where('name','LIKE',"%{$query}%")->get()->take(8);
        return response()->json($results);
    }
}
