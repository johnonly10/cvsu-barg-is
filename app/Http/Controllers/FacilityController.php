<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index() {
        $facilities = Facility::orderBy('created_at', 'DESC')->paginate(5);
        return view('admin.facilities.index', compact('facilities'));
    }

    public function create() {
       return view('admin.facilities.create');
    }

    public function store(Request $request) {
        // dd($request->all());
        $data = $request->all();
        $data['created_by'] = auth()->user()->id;
        $facility = Facility::create($data);
        exit;
        $facility = new Facility();
        $rental->name = $request->name;
        $rental->slug = Str::slug($request->name);
        $rental->description = $request->description;
        $rental->rules_and_regulations = $request->rules_and_regulations;
        $rental->price = in_array($request->name, $priceRequiredNames) ? $request->price : null;
        $rental->internal_price = in_array($request->name, $internalExternalRequiredNames) ? $request->internal_price : null;
        $rental->external_price = in_array($request->name, $internalExternalRequiredNames) ? $request->external_price : null;
        $rental->exclusive_price = $request->name === 'Swimming Pool' ? $request->exclusive_price : null;
        $rental->capacity = $request->capacity;
        $rental->status = $request->status;
        $rental->featured = $request->featured;
        $rental->sex = $request->sex;

        $current_timestamp = Carbon::now()->timestamp;

        // Ensure sex restriction for Male/Female Dormitories
        if ($request->name == 'Male Dormitory' && $request->sex != 'male') {
            return redirect()->back()->withErrors(['sex' => 'Only males can reserve in the Male Dormitory.'])->withInput();
        }

        if ($request->name == 'Female Dormitory' && $request->sex != 'female') {
            return redirect()->back()->withErrors(['sex' => 'Only females can reserve in the Female Dormitory.'])->withInput();
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateRentalThumbnailsImage($image, $imageName);
            $rental->image = $imageName;
        }

        // Handle gallery images
        $gallery_arr = [];
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            $allowedFileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedFileExtension);

                if ($gcheck) {
                    $gFileName = $current_timestamp . "." . $counter . '.' . $gextension;
                    $this->GenerateRentalThumbnailsImage($file, $gFileName);
                    array_push($gallery_arr, $gFileName);
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }

        $rental->images = $gallery_images;

        // Handle Requirements upload
        if ($request->hasFile('requirements')) {
            $requirementsFile = $request->file('requirements');
            $requirementsFileName = $current_timestamp . '-requirements.' . $requirementsFile->getClientOriginalExtension();
            if (Rental::where('requirements', $requirementsFileName)->exists()) {
                Log::warning('Requirements file name already exists: ' . $requirementsFileName);
                return redirect()->back()->withErrors(['requirements' => 'The Requirements file name already exists. Please rename the file.'])->withInput();
            }
            $destinationPath = public_path('uploads/rentals/files');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            $requirementsFile->move($destinationPath, $requirementsFileName);
            $rental->requirements = $requirementsFileName;
        }

        $rental->save();

        if (
            in_array($request->name, ['Male Dormitory', 'Female Dormitory', 'International House II']) &&
            !empty($request->room_number) && !empty($request->room_capacity)
        ) {
            foreach ($request->room_number as $index => $roomNumber) {
                DormitoryRoom::create([
                    'rental_id' => $rental->id,
                    'room_number' => $roomNumber,
                    'room_capacity' => $request->room_capacity[$index],
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ]);
            }
        }

        return redirect()->route('admin.rentals')->with('status', 'Rental has been added successfully!');
    }
}
