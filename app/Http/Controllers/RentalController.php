<?php

namespace App\Http\Controllers;


use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;
use App\Models\DormitoryRoom;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class RentalController extends Controller

{
    public function index() {
        $rentals = Rental::orderBy('created_at', 'DESC')->paginate(5);
        return view('rental', compact('rentals'));
    }
    
    public function show($rental_slug)
{
    $rental = Rental::where('slug', $rental_slug)->firstOrFail();
    $gallery_images = !empty($rental->images) ? explode(',', $rental->images) : [];

    return view('rentals_details', compact('rental', 'gallery_images'));
}

public function checkout(Request $request, $rental_id)
{
    if (!Auth::check()) {
        return redirect()->route("login");
    }

    // Retrieve rental details based on rental_id
    $rental = Rental::find($rental_id);
    if (!$rental) {
        return redirect()->back()->with('error', 'Rental not found');
    }

    // Capture the necessary inputs
    $internal_quantity = $request->input('internal_quantity', 0);
    $external_quantity = $request->input('external_quantity', 0);
    $total_price = $request->input('total_price', 0);
    $usage_type = $request->input('usage_type', 'individual_group');  // Default to 'individual_group'

    // Calculate pool_quantity
    $pool_quantity = $internal_quantity + $external_quantity;

    // Retrieve dormitory room details if applicable
    $dormitoryRoom = null;
    if (in_array($rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II'])) {
        $dormitoryRoom = DormitoryRoom::where('rental_id', $rental->id)->first();
    }

    // Pass variables to the checkout view
    return view('rentals_checkout', compact("rental", "dormitoryRoom", "pool_quantity", "internal_quantity", "external_quantity", "total_price", "usage_type"));
}


public function placeReservation(Request $request, $rentalId)
{
    // Ensure the user is logged in
    if (!Auth::check()) {
        return redirect()->route("login");
    }

    $rental = Rental::find($rentalId);
    if (!$rental) {
        return redirect()->back()->with('error', 'Rental not found');
    }
    $userSex = Auth::user()->sex;

    if ($rental->sex !== 'all' && $rental->sex !== $userSex) {
        Log::warning('Sex mismatch. Rental Sex: ' . $rental->sex . ', User Sex: ' . $userSex);
        return redirect()->back()->withErrors(['sex' => 'You cannot add this facility to the reservation due to sex eligable(male/female).'])->withInput();
    }

    // Define base validation rules
    $rules = [
        'internal_quantity' => 'required|integer|min:0',
        'external_quantity' => 'required|integer|min:0',
        'qualification' => 'required|file|mimes:pdf,doc,docx',
        'total_price' => 'required|numeric|min:0',
        'time_slot' => 'required|string',
        'usage_type' => 'required|in:individual_group,exclusive_use', // New validation rule for usage_type
    ];

    // Conditional validation rules based on rental name
    if ($rental->name === 'International House II') {
        // Add validation rules for International House II
        $rules['ih_start_date'] = 'required|date|after_or_equal:tomorrow';
        $rules['ih_end_date'] = 'required|date|after:ih_start_date';
    } elseif (in_array($rental->name, ['Male Dormitory', 'Female Dormitory'])) {
        // For Male and Female Dormitory, skip reservation_date validation
        $rules['reservation_date'] = 'nullable'; // Make it optional
    } else {
        // For other rentals, ensure reservation_date is required
        $rules['reservation_date'] = 'required|date|after_or_equal:today';
    }

    $validatedData = $request->validate($rules);

    // Capture quantities and usage_type from the request
    $internal_quantity = $request->input('internal_quantity', 0);
    $external_quantity = $request->input('external_quantity', 0);
    $pool_quantity = $internal_quantity + $external_quantity;
    $usage_type = $request->input('usage_type'); // Capture the usage_type

    // Calculate total price for International House II
    if ($rental->name === 'International House II') {
        $startDate = Carbon::parse($validatedData['ih_start_date']);
        $endDate = Carbon::parse($validatedData['ih_end_date']);
        $days = $startDate->diffInDays($endDate);
        $calculatedPrice = $days * $rental->price; // Assuming $rental->price holds the daily price

        // Validate the calculated price matches the input
        if ($validatedData['total_price'] != $calculatedPrice) {
            return back()->withErrors(['total_price' => 'Invalid total price calculation.'])->withInput();
        }

        // Handle dormitory room creation or update
        $dormitoryRoom = DormitoryRoom::where('rental_id', $rentalId)->first();

        if (!$dormitoryRoom) {
            $dormitoryRoom = DormitoryRoom::create([
                'rental_id' => $rentalId,
                'room_number' => 'Default Room', // Update as needed
                'room_capacity' => 1, // Update as needed
                'start_date' => $validatedData['ih_start_date'],
                'end_date' => $validatedData['ih_end_date'],
                'ih_start_date' => $validatedData['ih_start_date'],
                'ih_end_date' => $validatedData['ih_end_date'],
            ]);
        } else {
            $dormitoryRoom->update([
                'ih_start_date' => $validatedData['ih_start_date'],
                'ih_end_date' => $validatedData['ih_end_date'],
            ]);
        }
    }

    // Proceed with saving the reservation
    $reservation = Reservation::create([
        'user_id' => Auth::user()->id,
        'rental_id' => $rentalId,
        'reservation_date' => $validatedData['reservation_date'] ?? null, // Make reservation_date nullable
        'time_slot' => $validatedData['time_slot'],
        'rent_status' => 'pending',
        'payment_status' => 'pending',
        'total_price' => $validatedData['total_price'],
        'pool_quantity' => $pool_quantity, // Assign computed pool_quantity
        'internal_quantity' => $internal_quantity, // Assign internal_quantity from request
        'external_quantity' => $external_quantity, // Assign external_quantity from request
        'usage_type' => $usage_type, // Save the usage_type
    ]);

    // Handle qualification file upload
    if ($request->hasFile('qualification')) {
        $file = $request->file('qualification');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/rentals/files'), $filename);

        // Save the file to the rental
        $rental->qualification = $filename;
        $rental->save();
    }

    // Redirect with success message
    return redirect()->route('rentals.checkout', ['rental_id' => $rental->id])
                     ->with('success', 'Reservation successfully placed!');
}

public function checkPoolCapacity($rentalId, $date)
{
    $rental = Rental::find($rentalId);
    if (!$rental || $rental->name !== 'Swimming Pool') {
        return response()->json(['error' => 'Invalid rental or rental type'], 400);
    }

    $currentReservations = Reservation::where('rental_id', $rentalId)
        ->where('reservation_date', $date)
        ->sum('pool_quantity');

    return response()->json([
        'remaining_capacity' => max(0, $rental->capacity - $currentReservations),
    ]);
}

public function getReservations($rentalId)
{
    $reservations = Reservation::where('rental_id', $rentalId)
        ->where('user_id', Auth::user()->id)
        ->get(['reservation_date', 'rent_status']);

    return response()->json($reservations);
}

    
}