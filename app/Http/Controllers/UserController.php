<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function index()
    {
        return view('user.index');
    }

    public function about()
    {
        return view('about.index');
    }
    
    public function orders()
    {


        $orders = Order::where('user_id', Auth::user()->id)
                        ->whereNotIn('status', ['canceled', 'pickedup']) // Exclude canceled or picked-up orders
                        ->orderBy('created_at', 'DESC')
                        ->paginate(10);
        
        return view('user.orders', compact('orders'));
    }

 
    public function order_details($order_id)
    {
        $order = Order::where('user_id', Auth::user()->id)->where('id', $order_id)->first();
        if ($order) {
            $orderItems = OrderItem::where('order_id', $order->id)->orderBy('id')->paginate(12);
            $transaction = Transaction::where('order_id', $order->id)->first();
            return view('user.order_details', compact('order', 'orderItems', 'transaction'));
        } else {
            return redirect()->route('login');
        }
    }

    public function order_history()
    {
        $userId = Auth::user()->id;

        // Get the date 30 days ago
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Fetch only accepted, canceled, or picked-up orders for history
        $orders = Order::where('user_id', $userId)
                        ->whereIn('status', ['accepted', 'canceled', 'pickedup']) // Filter by history status
                        ->where('created_at', '>=', $thirtyDaysAgo) // Filter orders from the last 30 days
                        ->orderBy('created_at', 'desc') // Order by the latest first
                        ->paginate(10);

        return view('user.order_history', compact('orders'));
    }
    
    public function show_profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function profile_edit($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('user.profile')->with('error', 'User not found.');
        }
        return view('user.profile-edit', compact('user'));
    }

    public function profile_update(Request $request)
    {
        $user = Auth::user();
        $role = $request->input('role', $user->role); 

        // Basic validation for all users
        $validationRules = [
            'phone_number' => 'required|numeric|digits:10',
             'sex' => 'required|in:male,female',
        ];

        // Conditionally validate role change if allowed
        if ($user->role_change_allowed) {
            $validationRules['role'] = 'required|string|in:student,employee,non-employee';
        }
        

        // Additional validations for specific roles
        if ($role === 'student') {
            $validationRules['department'] = 'required|string';
            $validationRules['year_level'] = 'required|string|in:1st Year,2nd Year,3rd Year,4th Year, 5th Year';
            $validationRules['course'] = 'required|string';
        } elseif ($role === 'professor') {
            $validationRules['department'] = 'required|string';
        }

        // Validate the request
        $validatedData = $request->validate($validationRules);

        // If role changes, disable future role changes
        if ($user->role !== $role && $user->role_change_allowed) {
            $user->role = $role;
            $user->role_change_allowed = false; // Prevent future role changes
        }

        // Update user fields
        $user->phone_number = $validatedData['phone_number'];
        $user->sex = $validatedData['sex'];

        // Handle role-specific data updates
        if ($role === 'student') {
            $user->year_level = $validatedData['year_level'];
            $user->department = $validatedData['department'];
            $user->course = $validatedData['course'];
        } elseif ($role === 'professor') {
            $user->year_level = null;
            $user->department = $validatedData['department'];
            $user->course = null;
        } else {
            // Clear fields for 'others'
            $user->year_level = null;
            $user->department = null;
            $user->course = null;
        }

        // Save the user data
        $user->save();

        // Redirect to the appropriate page
        if (Cart::instance('cart')->count() > 0) {
            return redirect()->route('cart.checkout')->with('success', 'Profile updated successfully.');
        } else {
            return redirect()->route('shop.index')->with('success', 'Profile updated successfully.');
        }
    }




    public function profile_image_edit()
    {
        $user = Auth::user();
        return view('user.profile-edit-image', compact('user'));
    }


    public function profile_image_update(Request $request)
    {
        $request->validate([
            'profile_image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');

            if ($user->profile_image) {

                Storage::disk('public')->delete($user->profile_image);
            }

            $user->profile_image = $imagePath;
            $user->save();

            return redirect()->route('user.profile')->with('success', 'Profile image updated successfully.');
        }

        return redirect()->route('user.profile')->with('error', 'No image uploaded.');
    }


    public function profile_image_delete()
    {
        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);

            $user->profile_image = null;
            $user->save();

            return redirect()->route('user.profile')->with('success', 'Profile image deleted successfully.');
        }

        return redirect()->route('user.profile')->with('error', 'No profile image to delete.');
    }

    public function account_reservation()
    {
        $reservations = Reservation::where('user_id', Auth::user()->id)
            ->whereNotIn('rent_status', ['completed', 'canceled']) // Exclude completed and canceled reservations
            ->orderBy('created_at', 'DESC')
            ->paginate(5);
    
        return view('user.reservation', compact('reservations'));
    }
        public function account_reservation_details($reservation_id)
        {
    
            $reservation = Reservation::where('user_id', Auth::user()->id)->find($reservation_id);        

            if ($reservation) {
                
                return view('user.reservation-details', compact('reservation'));
            } else {
                return redirect()->route('user.reservation.history')->with('error', 'Reservation not found.');
            }
        }

        

        // public function account_cancel_reservation(Request $request)
        // {
        //     // Find the reservation by ID and ensure it belongs to the authenticated user
        //     $reservation = Reservation::where('id', $request->reservation_id)
        //                                 ->where('user_id', Auth::id())
        //                                 ->first();
        
        //     if (!$reservation) {
        //         return back()->with('error', 'Reservation not found.');
        //     }
        
        //     // Update the rent_status, payment_status, and canceled_date fields
        //     $reservation->rent_status = "canceled";
        //     $reservation->payment_status = "canceled";
        //     $reservation->canceled_date = Carbon::now();
        //     $reservation->save();
        
        //     return redirect()->route('user.reservation.history')->with("status", "Reservation has been cancelled successfully!");
        // }
        


            
        public function reservation_history()
        {
            $userId = Auth::user()->id;

            // Get the date 30 days ago
            $thirtyDaysAgo = Carbon::now()->subDays(30);

         // Fetch reservations that are reserved, completed, or canceled
            $reservations = Reservation::where('user_id', $userId)
            ->whereIn('rent_status', ['completed', 'canceled']) // Include only completed or canceled reservations
            ->where(function ($query) use ($thirtyDaysAgo) {
                $query->where('created_at', '>=', $thirtyDaysAgo)
                    ->orWhere('canceled_date', '>=', $thirtyDaysAgo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

            return view('user.reservation_history', compact('reservations'));
        }   
        




}
