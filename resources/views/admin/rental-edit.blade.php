@extends('layouts.admin')

@section('content')
<style>
    .file-name-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 5px;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        text-align: center;
        font-size: 12px;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .room-error-message {
    color: red;
    font-size: 12px;
    margin-top: 5px;
    display: block;
    }


    .item {
        display: flex;
        justify-content: center; 
        align-items: center; 
        position: relative;
    }

    .upload-image img {
        width: 100%; 
        height: auto; 
        object-fit: cover; 
        border-radius: 8px; 
    }

    .upload-image {
        position: relative;
        overflow: hidden;
        border-radius: 8px; 
        width: 100%; /* Ensures consistent width */
        min-height: 150px; /* Fixed minimum height to prevent size change */
        background-color: #f9f9f9; /* Optional: Background color for better visibility */
    }

    .uploadfile {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        cursor: pointer;
        padding: 10px;
        box-sizing: border-box;
        text-align: center;
    }

    .uploadfile .icon {
        font-size: 24px;
        margin-bottom: 5px;
    }

    .uploadfile .body-text,
    .uploadfile .text-tiny {
        font-size: 14px;
        color: #555;
    }

    .remove-upload {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: rgba(20, 19, 20, 0.8);
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 4px; 
        display: none; /* Initially hidden */
        z-index: 10;
    }

    .remove-upload.show {
        display: block; /* Show when needed */
    }

    /* Optional: Styling for gallery images */
    .gitems {
        position: relative;
        margin-right: 10px;
    }

    .gitems img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 4px;
    }

    /* Room Field Container */
    .room-field-container {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
        position: relative;
    }

    .remove-room {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #ff4d4d;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 4px;
    }
</style>

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Edit Rental</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <a href="{{ route('admin.rentals') }}">
                        <div class="text-tiny">Rentals</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Edit Rental</div>
                </li>
            </ul>
        </div>

        <!-- form-edit-rental -->
        <form action="{{ route('admin.rental.update', $rental->id) }}" class="tf-section-2 form-edit-rental" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="{{ $rental->id }}">
            @csrf
            @method('PUT')
            
            <div class="wg-box">

                {{-- Name --}}
                <fieldset class="name">
                    <div class="body-title mb-10">Rental name <span class="tf-color-1">*</span></div>
                    <div class="select mb-10">
                        <select name="name" id="rentalNameSelect" required>
                            <option value="">Select a rental name</option> 
                            <option value="Male Dormitory" {{ $rental->name == 'Male Dormitory' ? 'selected' : '' }}>Male Dormitory</option>
                            <option value="Female Dormitory" {{ $rental->name == 'Female Dormitory' ? 'selected' : '' }}>Female Dormitory</option>
                            <option value="International House II" {{ $rental->name == 'International House II' ? 'selected' : '' }}>International House II</option>
                            <option value="International Convention Center" {{ $rental->name == 'International Convention Center' ? 'selected' : '' }}>International Convention Center</option>
                            <option value="Rolle Hall" {{ $rental->name == 'Rolle Hall' ? 'selected' : '' }}>Rolle Hall</option>
                            <option value="Swimming Pool" {{ $rental->name == 'Swimming Pool' ? 'selected' : '' }}>Swimming Pool</option>
                        </select>
                    </div>
                </fieldset>
                @error('name') 
                    <span class="alert alert-danger text-center">{{ $message }}</span> 
                @enderror
                
                {{-- Description --}}
                <fieldset class="description">
                    <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
                    <textarea class="mb-10" name="description" required>{{ $rental->description }}</textarea>
                </fieldset>
                @error('description') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

                <fieldset class="rules_and_regulations">
                    <div class="body-title mb-10">Rules and Regulation <span class="tf-color-1">*</span></div>
                    <textarea class="mb-10" id="rules" name="rules_and_regulations" placeholder="rules_and_regulations" required>{{ old('rules_and_regulations', $rental->rules_and_regulations) }}</textarea>
                </fieldset>
                @error('rules_and_regulations')
                    <span class="alert alert-danger text-center">{{ $message }} </span>
                @enderror

                <!-- Requirements formatted like upload image -->
                <fieldset>
                    <div class="body-title mb-10">Requirements <span class="tf-color-1">*</span></div>
                    <div class="upload-image flex-grow">
                        <div class="item" id="requirementsPreview" style="display: {{ $rental->requirements ? 'block' : 'none' }};">
                            @if($rental->requirements)
                                <a href="{{ asset('uploads/rentals/'.$rental->requirements) }}" target="_blank">{{ $rental->requirements }}</a>
                            @endif
                            <button type="button" class="remove-upload {{ $rental->requirements ? 'show' : '' }}" onclick="removeUpload('requirementsPreview', 'requirementsFile')">Remove</button>
                        </div>
                        <div id="upload-requirements" class="item up-load">
                            <label class="uploadfile" for="requirementsFile">
                                <span class="icon"><i class="icon-upload-cloud"></i></span>
                                <span class="body-text">Select your Requirements file here or click to browse</span>
                                <input type="file" id="requirementsFile" name="requirements" accept=".pdf,.doc,.docx,.jpg,.png" {{ $rental->requirements ? '' : 'required' }}>
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('requirements') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

            </div>

            <div class="wg-box">

                <!-- Image upload -->
                <fieldset>
                    <div class="body-title">Upload main image <span class="tf-color-1">*</span></div>
                    <div class="upload-image flex-grow">
                        <div class="item" id="imgpreview" style="position: relative; display: {{ $rental->image ? 'block' : 'none' }};">
                            <img src="{{ asset('uploads/rentals/'.$rental->image) }}" id="preview-img" class="effect8" alt="{{ $rental->name }}">
                            <button type="button" class="remove-upload {{ $rental->image ? 'show' : '' }}" onclick="removeUpload('imgpreview', 'myFile')">Remove</button>
                        </div>
                        <div id="upload-file" class="item up-load">
                            <label class="uploadfile" for="myFile">
                                <span class="icon"><i class="icon-upload-cloud"></i></span>
                                <span class="body-text">Select your main image here or click to browse</span>
                                <input type="file" id="myFile" name="image" accept="image/*" {{ $rental->image ? '' : 'required' }}>
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('image') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

                <!-- Gallery images upload -->
                <fieldset>
                    <div class="body-title mb-10">Upload Gallery Images</div>
                    <div class="upload-image mb-16 flex-grow" id="gallery-container">
                        @if($rental->images)
                            @foreach(explode(",", $rental->images) as $img)
                                <div class="item gitems">
                                    <img src="{{ asset('uploads/rentals/'.trim($img)) }}" style="width: 100px; height: 100px; object-fit: cover;" />
                                    <button type="button" class="remove-upload show" onclick="removeGalleryImage(this, 'gFile')">Remove</button>
                                </div>
                            @endforeach
                        @endif
                        <div id="galUpload" class="item up-load">
                            <label class="uploadfile" for="gFile">
                                <span class="icon"><i class="icon-upload-cloud"></i></span>
                                <span class="text-tiny">Select your gallery images here or click to browse</span>
                                <input type="file" id="gFile" name="images[]" accept="image/*" multiple>
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('images') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

                

                <!-- Price Fields -->
                <div id="priceFields" style="display: block;">
                    <fieldset class="name">
                        <div class="body-title mb-10">Price <span class="tf-color-1">*</span></div>
                        <input type="number" name="price" value="{{ $rental->price }}" required>
                    </fieldset>
                </div>
                @error('price') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror



                <!-- Internal and External Price Fields -->
                <div id="internalExternalPriceFields" style="display: none;">
                    <fieldset class="name">
                        <div class="body-title mb-10">Capacity <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="number" placeholder="Enter capacity" name="capacity" value="{{ $rental->capacity }}" required>
                    </fieldset>
                    @error('capacity') <span class="alert alert-danger text-center">{{$message}} </span> @enderror
                    <div class="cols gap22">
                        <fieldset class="name">
                            <div class="body-title mb-10">Internal Price <span class="tf-color-1">*</span></div>
                            <input class="mb-10" type="text" placeholder="Enter internal price" name="internal_price" value="{{ $rental->internal_price }}" required>
                        </fieldset>
                        @error('internal_price') <span class="alert alert-danger text-center">{{$message}} </span> @enderror

                        <fieldset class="name">
                            <div class="body-title mb-10">External Price <span class="tf-color-1">*</span></div>
                            <input class="mb-10" type="text" placeholder="Enter external price" name="external_price" value="{{ $rental->external_price }}" required>
                        </fieldset>
                        @error('external_price') <span class="alert alert-danger text-center">{{$message}} </span> @enderror
                        <fieldset class="name">
                            <div class="body-title mb-10">Exclusive Price <span class="tf-color-1">*</span></div>
                            <input class="mb-10" type="text" placeholder="Enter exclusive price" name="exclusive_price" value="{{ $rental->exclusive_price }}">
                        </fieldset>
                        @error('exclusive_price') <span class="alert alert-danger text-center">{{$message}} </span> @enderror
                    </div>
                </div>

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">Status</div>
                        <div class="select mb-10">
                            <select name="status">
                                <option value="available" {{ $rental->status == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="not available" {{ $rental->status == 'not available' ? 'selected' : '' }}>Not Available</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('status') <span class="alert alert-danger text-center">{{$message}} </span> @enderror
                    <fieldset class="name">
                        <div class="body-title mb-10">Featured</div>
                        <div class="select mb-10">
                            <select name="featured">
                                <option value="0" {{ $rental->featured == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ $rental->featured == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('featured') <span class="alert alert-danger text-center">{{$message}} </span> @enderror
                </div>

                <div class="cols gap10">
                    <button class="tf-button w-full" type="submit">Update Rental</button>
                </div>
            </div>

    <!-- Conditional Fields for Dormitory Rentals (Male Dormitory, Female Dormitory, International House II) -->
    <div class="wg-box" style="display: {{ in_array($rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']) ? 'block' : 'none' }}">
        <div id="dormitoryFields">
            <h4>Room Details</h4>
            <div id="roomContainer">
                @if($rental->dormitoryRooms)
                    @foreach($rental->dormitoryRooms as $room)
                        <div class="room-field-container">
                            <input type="hidden" name="room_id[]" value="{{ $room->id }}">
                            <fieldset class="name">
                                <div class="body-title mb-10">Room Number <span class="tf-color-1">*</span></div>
                                <input type="text" name="room_number[]" value="{{ $room->room_number }}" required>
                            </fieldset>
                            <fieldset class="name">
                                <div class="body-title mb-10">Room Capacity <span class="tf-color-1">*</span></div>
                                <input type="number" name="room_capacity[]" value="{{ $room->room_capacity }}" required>
                            </fieldset>
                            <fieldset class="name">
                                <div class="body-title mb-10">Start Date <span class="tf-color-1">*</span></div>
                                <input type="date" name="start_date[]" value="{{ $room->start_date }}" required min="{{ date('Y-m-d') }}">
                            </fieldset>
                            <fieldset class="name">
                                <div class="body-title mb-10">End Date <span class="tf-color-1">*</span></div>
                                <input type="date" name="end_date[]" value="{{ $room->end_date }}" required min="{{ date('Y-m-d') }}">
                            </fieldset>
                            <button type="button" class="remove-room">Remove Room</button>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="addnewroom"></div>
            <button type="button" class="add-room-button">Add Room</button>
    
            <!-- Submit Button -->
        <div class="d-grid gap10 mt-5">
            <button class="dorm-btn btn-primary w-full" type="submit">Update Rental</button>
        </div>
    </div>



    
            
            

           



        </form>
        <!-- /form-edit-rental -->
    </div>
    <!-- /main-content-wrap -->
</div>
<!-- /main-content-wrap -->
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
     $(function() {
            tinymce.init({
                selector: '#rules',
                setup: function(editor) {
                    editor.on('change', function(e) {
                        tinyMCE.triggerSave();

                        var sd_data = $('#short_description').val();

                    });
                },

                height: 300,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help'
            });
        });
    // Function to toggle required fields based on rental type
    function togglePriceFields() {
        const selectedName = $('#rentalNameSelect').val();
        
        const priceField = $('#priceFields');
        const internalExternalPriceFields = $('#internalExternalPriceFields');
        const capacityField = $('input[name="capacity"]');
        const internalPriceField = $('input[name="internal_price"]');
        const externalPriceField = $('input[name="external_price"]');
        const exclusivePriceField = $('input[name="exclusive_price"]');

        // Rental types that require only the price field
        const priceRequiredRentals = ['Male Dormitory', 'Female Dormitory', 'International House II'];

        // Rental types that require capacity, internal price, and external price fields
        const internalExternalRequiredRentals = ['International Convention Center', 'Rolle Hall', 'Swimming Pool'];

        // Toggle fields based on rental type
        if (priceRequiredRentals.includes(selectedName)) {
            priceField.show();
            internalExternalPriceFields.hide();
            
            // Set 'required' attributes
            $('input[name="price"]').prop('required', true);
            capacityField.prop('required', false);
            internalPriceField.prop('required', false);
            externalPriceField.prop('required', false);
        } else if (internalExternalRequiredRentals.includes(selectedName)) {
            priceField.hide();
            internalExternalPriceFields.show();
            
            // Set 'required' attributes for internal/external prices and capacity
            $('input[name="price"]').prop('required', false);
            capacityField.prop('required', true);
            internalPriceField.prop('required', true);
            externalPriceField.prop('required', true);
            exclusivePriceField.prop('required', true);
        } else {
            // Hide all fields if the rental type doesn't match any condition
            priceField.hide();
            internalExternalPriceFields.hide();
            
            // Remove 'required' from all fields
            $('input[name="price"]').prop('required', false);
            capacityField.prop('required', false);
            internalPriceField.prop('required', false);
            externalPriceField.prop('required', false);
        }
    }


    // Reusable function to set up upload previews and remove buttons
    function setupUploadPreview(inputSelector, previewSelector, removeButtonSelector, isImage = false) {
        $(inputSelector).on("change", function(e) {
            const [file] = e.target.files;
            if (file) {
                if (isImage) {
                    $(previewSelector + " img").attr('src', URL.createObjectURL(file));
                } else {
                    // For documents or other files
                    $(previewSelector).find('a').remove();
                    $(previewSelector).append('<a href="' + URL.createObjectURL(file) + '" target="_blank">' + file.name + '</a>');
                }
                $(previewSelector).show();
                $(removeButtonSelector).addClass('show');
            }
        });

        $(removeButtonSelector).on("click", function() {
            $(previewSelector).hide();
            if (isImage) {
                $(previewSelector + " img").attr('src', '');
            } else {
                $(previewSelector).find('a').remove();
            }
            $(inputSelector).val('');
            $(this).removeClass('show');
        });
    }

    // Remove function for gallery images and reset input field
    function removeGalleryImage(button, inputId) {
        $(button).parent('.gitems').remove(); // Remove the gallery image
        // Optionally, you can add logic to mark the image for deletion on the backend
        // For example, append a hidden input with the image ID to delete
    }

    $(document).ready(function() {
        // Initial call to set up fields based on the default selected name
        togglePriceFields();

        // Toggle fields when the rental type selection changes
        $('#rentalNameSelect').on('change', togglePriceFields);

        
        // Set up upload previews
        setupUploadPreview("#myFile", "#imgpreview", "#imgpreview .remove-upload", true);
        setupUploadPreview("#rulesFile", "#rulesPreview", "#rulesPreview .remove-upload", false);
        setupUploadPreview("#requirementsFile", "#requirementsPreview", "#requirementsPreview .remove-upload", false);

        // Handle gallery image upload and preview filenames inside the picture area
        $("#gFile").on("change", function(e) {
            const gphotos = this.files;
            $("#galUpload").removeClass('up-load'); 
            let imgCount = 0;

            // Clear existing gallery images except the ones marked for deletion
            $('#gallery-container .gitems').not('[data-delete="true"]').remove();

            $.each(gphotos, function(key, val) {
                imgCount++;
                const fileName = val.name; // Get file name
                $('#galUpload').before(`
                    <div class="item gitems">
                        <img src="${URL.createObjectURL(val)}" style="width: 100px; height: 100px; object-fit: cover;" />
                        <p class="file-name-overlay">${fileName}</p>
                        <button type="button" class="remove-upload show" onclick="removeGalleryImage(this, 'gFile')">Remove</button>
                    </div>
                `);
            });

            // Maintain the size of the upload section
            if (imgCount > 2) {
                $('#galUpload').css('flex-basis', '100%');
            } else {
                $('#galUpload').css('flex-basis', 'auto');
            }
        });

    });




    function toggleUpdateButton() {
        const selectedName = $('#rentalNameSelect').val();
        const updateButton = $('.tf-button[type="submit"]');

        // List of rental names to hide the button
        const restrictedRentals = ['Male Dormitory', 'Female Dormitory', 'International House II'];

        if (restrictedRentals.includes(selectedName)) {
            updateButton.hide(); // Hide the button
        } else {
            updateButton.show(); // Show the button
        }
    }

    $(document).ready(function() {
        // Call the function on page load to handle pre-selected values
        toggleUpdateButton();

        // Attach the toggle function to the rental name dropdown change event
        $('#rentalNameSelect').on('change', toggleUpdateButton);
    });

    
    function validateRoomFields() {
    let isValid = true; // Flag to check if all validations pass
    const roomContainers = $('.room-field-container'); // Select all room containers

    // Clear any existing error messages
    $('.room-error-message').remove();

    roomContainers.each(function() {
        const roomNumber = $(this).find('input[name="room_number[]"]').val().trim();
        const roomCapacity = $(this).find('input[name="room_capacity[]"]').val().trim();

        // Check if Room Number is empty
        if (!roomNumber) {
            isValid = false;
            $(this)
                .find('input[name="room_number[]"]')
                .after('<span class="room-error-message" style="color: red; font-size: 12px;">Room Number is required.</span>');
        }

        // Check if Room Capacity is empty
        if (!roomCapacity) {
            isValid = false;
            $(this)
                .find('input[name="room_capacity[]"]')
                .after('<span class="room-error-message" style="color: red; font-size: 12px;">Room Capacity is required.</span>');
        }
    });

    return isValid;
    }

    $(document).ready(function() {
        // Attach validation to form submit
        $('.form-edit-rental').on('submit', function(e) {
            const isRoomFieldsValid = validateRoomFields(); // Validate room fields

            if (!isRoomFieldsValid) {
                e.preventDefault(); // Prevent form submission if validation fails
                alert('Please correct the errors in the Room fields before submitting.');
            }
        });
    });

    $(document).ready(function() {
        // Function to check for duplicate room numbers
        function checkForDuplicateRoomNumbers() {
            var roomNumbers = [];
            var isDuplicate = false;
            
            // Collect all room numbers entered in the form
            $('input[name="room_number[]"]').each(function() {
                var roomNumber = $(this).val().trim();
                
                // Check if the room number is already in the array (duplicate)
                if (roomNumbers.includes(roomNumber)) {
                    isDuplicate = true;
                    return false;  // Exit loop if duplicate is found
                } else {
                    roomNumbers.push(roomNumber);  // Add room number to array
                }
            });
            
            return isDuplicate;
        }

        // Form submit validation
        $('.form-edit-rental').on('submit', function(e) {
            // Check for duplicate room numbers
            if (checkForDuplicateRoomNumbers()) {
                e.preventDefault();  // Prevent form submission
                
                // Show an error message
                alert("Room numbers must be unique. Please check for duplicates.");
                return false;  // Stop further form submission
            }

            // No duplicates found, allow form submission
            return true;
        });
    });

    $(document).ready(function () {
    const today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format

    // Function to add a new room container dynamically
    function addRoomContainer() {
        const newRoomContainer = `
        <div class="room-field-container">
            <fieldset class="name">
                <div class="body-title mb-10">Room Number <span class="tf-color-1">*</span></div>
                <input type="text" name="room_number[]" placeholder="Enter room number" required>
            </fieldset>
            <fieldset class="name">
                <div class="body-title mb-10">Room Capacity <span class="tf-color-1">*</span></div>
                <input type="number" name="room_capacity[]" placeholder="Enter room capacity" required>
            </fieldset>
            <fieldset class="name">
                <div class="body-title mb-10">Start Date <span class="tf-color-1">*</span></div>
                <input type="date" name="start_date[]" required min="${today}">
            </fieldset>
            <fieldset class="name">
                <div class="body-title mb-10">End Date <span class="tf-color-1">*</span></div>
                <input type="date" name="end_date[]" required min="${today}">
            </fieldset>
            <button type="button" class="remove-room">Remove Room</button>
        </div>
        `;
        $('.addnewroom').append(newRoomContainer);
    }


    // Event listener for the "Add Room" button
    $('.add-room-button').on('click', function () {
        addRoomContainer();
    });
});

$(document).on('click', '.remove-room', function () {
        const roomContainer = $(this).closest('.room-field-container');
        const roomId = roomContainer.find('input[name="room_id[]"]').val();

        if (roomId) {
            // Mark the room for deletion by appending a hidden input to the form
            $('<input>').attr({
                type: 'hidden',
                name: 'removed_rooms[]',
                value: roomId
            }).appendTo('.form-edit-rental');
        }

        // Remove the room container from the DOM
        roomContainer.remove();
    });

</script>


@endpush
