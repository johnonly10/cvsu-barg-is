@extends('layouts.admin')
@section('content')
    <style>

    .remove-btn:hover {
        background-color: darkred;
    }



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
        }

        .uploadfile {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            cursor: pointer;
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
            display: none;
            /* Initially hidden */
        }

        .remove-upload.show {
            display: block;
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

    <!-- main-content-wrap --> 
    <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Add Rental</h3>
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
                        <div class="text-tiny">Add rental</div>
                    </li>
                </ul>
            </div>
            <!-- form-add-rental -->
            <form action="{{ route('admin.rental.store') }}" class="tf-section-2 form-add-rental" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="wg-box">

                    {{-- Name --}}
                    <fieldset class="name">
                        <div class="body-title mb-10">Rental name <span class="tf-color-1">*</span></div>
                        <div class="select mb-10">
                            <select name="name" id="rentalNameSelect" required>
                                <option value="">Select a rental name</option>
                                <option value="Male Dormitory" {{ old('name') == 'Male Dormitory' ? 'selected' : '' }}>Male
                                    Dormitory</option>
                                <option value="Female Dormitory" {{ old('name') == 'Female Dormitory' ? 'selected' : '' }}>
                                    Female Dormitory</option>
                                <option value="International House II"
                                    {{ old('name') == 'International House II' ? 'selected' : '' }}>International House II
                                </option>
                                <option value="International Convention Center"
                                    {{ old('name') == 'International Convention Center' ? 'selected' : '' }}>International
                                    Convention Center</option>
                                <option value="Rolle Hall" {{ old('name') == 'Rolle Hall' ? 'selected' : '' }}>Rolle Hall
                                </option>
                                <option value="Swimming Pool" {{ old('name') == 'Swimming Pool' ? 'selected' : '' }}>
                                    Swimming Pool</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <div class="gap22 cols">
                        <fieldset class="sex">
                            <div class="body-title mb-10">Sex Category <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" name="sex" required>
                                    <option value="">Choose Sex category</option>
                                    <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="all" {{ old('sex') === 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    @error('sex')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                    
                    {{-- Description --}}
                    <fieldset class="description">
                        <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10" name="description" placeholder="Description" tabindex="0" aria-required="true"
                            required="">{{ old('description') }}</textarea>
                    </fieldset>
                    @error('description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    <fieldset class="rules_and_regulations">
                        <div class="body-title mb-10">Rules and Regulation <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10" id="rules" name="rules_and_regulations" placeholder="rules_and_regulations" tabindex="0"
                            aria-required="true" required="">{{ old('rules_and_regulations') }}</textarea>
                    </fieldset>
                    @error('rules_and_regulations')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <!-- Requirements formatted like upload image -->
                    <fieldset>
                        <div class="body-title mb-10">Requirements <span class="tf-color-1">*</span></div>
                        <div class="upload-image flex-grow">
                            <div class="item" id="requirementsPreview" style="display:none">
                                <img src="{{ asset('images/upload/upload-1.png') }}" id="requirements-preview-img"
                                    class="effect8" alt="">
                                <button type="button" class="remove-upload"
                                    onclick="removeUpload('requirementsPreview', 'requirementsFile')">Remove</button>
                            </div>
                            <div id="upload-requirements" class="item up-load">
                                <label class="uploadfile" for="requirementsFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="body-text">Select your Requirements file here or click to browse</span>
                                    <input type="file" id="requirementsFile" name="requirements"
                                        accept=".pdf,.doc,.docx,.jpg,.png" required>
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('requirements')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                </div>

                <div class="wg-box">
                    <!-- Image upload -->
                    <fieldset>
                        <div class="body-title">Upload main image <span class="tf-color-1">*</span></div>
                        <div class="upload-image flex-grow">
                            <div class="item" id="imgpreview" style="display:none">
                                <img src="{{ asset('images/upload/upload-1.png') }}" id="preview-img" class="effect8"
                                    alt="">
                                <button type="button" class="remove-upload"
                                    onclick="removeUpload('imgpreview', 'myFile')">Remove</button>
                            </div>
                            <div id="upload-file" class="item up-load">
                                <label class="uploadfile" for="myFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="body-text">Select your main image here or click to browse</span>
                                    <input type="file" id="myFile" name="image" accept="image/*" required>
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('image')<span class="alert alert-danger text-center">{{ $message }} </span>@enderror


                    <!-- Gallery images upload -->
                    <fieldset>
                        <div class="body-title mb-10">Upload Gallery Images</div>
                        <div class="upload-image mb-16 flex-grow" id="gallery-container">
                            <div id="galUpload" class="item up-load">
                                <label class="uploadfile" for="gFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="text-tiny">Select your images here or click to browse</span>
                                    <input type="file" id="gFile" name="images[]" accept="image/*" multiple onchange="handleGalleryFiles(this.files)">
                                </label>
                            </div>
                            <div id="image-preview-container"></div>
                        </div>
                    </fieldset>

                    @error('images') 
                        <span class="alert alert-danger text-center">{{$message}} </span> 
                    @enderror

                    <!-- Price Fields -->
                    <div id="priceFields">
                        <!-- Price -->
                        <div class="cols gap22 price-field">
                            <fieldset class="name">
                                <div class="body-title mb-10">Price <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="number" placeholder="Enter price" name="price"
                                    tabindex="0" value="{{ old('price') }}" aria-required="true" min=0>
                            </fieldset>
                            @error('price')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror
                        </div>
                    </div>

                   
                    
                    <!-- Internal and External Price Fields -->
                    <div id="internalExternalPriceFields" style="display: none;">
                        <fieldset class="name">
                            <div class="body-title mb-10">Capacity <span class="tf-color-1">*</span></div>
                            <input class="mb-10" type="number" placeholder="Enter capacity" name="capacity"
                                tabindex="0" value="{{ old('capacity') }}" aria-required="true">
                        </fieldset>
                        @error('capacity')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                        <div class="cols gap22">
                            <!-- Internal Price -->
                            <fieldset class="name">
                                <div class="body-title mb-10">Internal Price <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="text" placeholder="Enter internal price"
                                    name="internal_price" tabindex="0" value="{{ old('internal_price') }}"
                                    aria-required="true">
                            </fieldset>
                            @error('internal_price')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror
                            

                            <!-- External Price -->
                            <fieldset class="name">
                                <div class="body-title mb-10">External Price <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="text" placeholder="Enter external price"
                                    name="external_price" tabindex="0" value="{{ old('external_price') }}"
                                    aria-required="true">
                            </fieldset>
                            @error('external_price')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror

                            <fieldset class="name">
                                <div class="body-title mb-10">Exclusive Price <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="text" placeholder="Enter exclusive price"
                                    name="exclusive_price" tabindex="0" value="{{ old('exclusive_price') }}"
                                    aria-required="true">
                            </fieldset>
                            @error('exclusive_price')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror
                        </div>


                    </div>

                    <div class="cols gap22">
                        <fieldset class="name">
                            <div class="body-title mb-10">Status</div>
                            <div class="select mb-10">
                                <select name="status">
                                    <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>
                                        Available</option>
                                    <option value="not available"
                                        {{ old('status') == 'not available' ? 'selected' : '' }}>Not Available</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('status')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror

                        <fieldset class="name">
                            <div class="body-title mb-10">Featured</div>
                            <div class="select mb-10">
                                <select name="featured">
                                    <option value="0" {{ old('featured') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('featured') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('featured')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                    </div>

                    <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">Add Rental</button>
                    </div>
                </div>

                <div class="wg-box">
                    <!-- Dynamic Room Fields Section -->
                    <div id="dormitoryFields" style="display: none;">
                        <h4>Room Details</h4>
                        <div id="roomContainer">
                            <!-- Initial Room Fields can be added here if needed -->
                        </div>
                        <button type="button" onclick="addRoomContainer()">Add Room</button>

                        <!-- Start and End Date Fields (Initially Hidden) -->
                        <div id="startEndDateFields" style="display: none;">
                        <div class="cols gap22 mt-5">
                            <!-- Start Date -->
                            <fieldset class="name">
                                <div class="body-title mb-10">Start Date <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="date" id="start_date" name="start_date" tabindex="0" value="{{ old('start_date') }}">
                            </fieldset>
                            @error('start_date')
                                <span class="alert alert-danger text-center">{{ $message }}</span>
                            @enderror

                            <!-- End Date -->
                            <fieldset class="name">
                                <div class="body-title mb-10">End Date <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="date" id="end_date" name="end_date" tabindex="0" value="{{ old('end_date') }}">
                            </fieldset>
                            @error('end_date')
                                <span class="alert alert-danger text-center">{{ $message }}</span>
                            @enderror
                        </div>
                            <div class="d-grid gap10">
                                <button class="dorm-btn btn-primary w-full" type="submit">Add Rental</button>
                            </div>
                        </div>
                    </div>
                    
                </div>

                {{-- <div class="wg-box">
                    <div class="additionaldorm">
                </div> --}}



            </form>
            <!-- /form-add-rental -->
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

    $(document).ready(function() {
        // Set the min attribute for start and end date fields to today's date
        const today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
        $('input[name="start_date"]').attr('min', today);
        $('input[name="end_date"]').attr('min', today);

        // Function to toggle price fields based on selected rental name
        function togglePriceFields() {
            var selectedName = $('#rentalNameSelect').val();
            var priceRequiredNames = ['Male Dormitory', 'Female Dormitory', 'International House II'];
            var internalExternalRequiredNames = ['International Convention Center', 'Rolle Hall', 'Swimming Pool'];

            if (priceRequiredNames.includes(selectedName)) {
                $('#priceFields').show();
                $('#internalExternalPriceFields').hide();
                $('input[name="internal_price"]').val('');
                $('input[name="external_price"]').val('');
            } else if (internalExternalRequiredNames.includes(selectedName)) {
                $('#priceFields').hide();
                $('#internalExternalPriceFields').show();
                $('input[name="price"]').val('');
            } else {
                $('#priceFields').hide();
                $('#internalExternalPriceFields').hide();
                $('input[name="price"]').val('');
                $('input[name="internal_price"]').val('');
                $('input[name="external_price"]').val('');
            }
        }

        // Initial toggle on page load
        togglePriceFields();

        // Toggle on change
        $('#rentalNameSelect').on('change', function() {
            togglePriceFields();
        });

        // Handle image preview for the main image
        $("#myFile").on("change", function(e) {
            const [file] = this.files;
            if (file) {
                $("#imgpreview img").attr('src', URL.createObjectURL(file));
                $("#imgpreview").show();
                $("#imgpreview .remove-upload").show(); // Show the remove button
            }
        });

        // Handle gallery image upload and preview filenames inside the picture area
        $("#gFile").on("change", function(e) {
            const gphotos = this.files;
            $("#galUpload").removeClass('up-load');
            let imgCount = 0;

            // Clear existing gallery images
            $('#gallery-container .gitems').remove();

            $.each(gphotos, function(key, val) {
                imgCount++;
                const fileName = val.name; // Get file name
                $('#galUpload').before('<div class="item gitems"><img src="' + URL
                    .createObjectURL(val) +
                    '" style="width: 100px; height: 100px; object-fit: cover;" /><p class="file-name-overlay">' +
                    fileName +
                    '</p><button type="button" class="remove-upload" onclick="removeGalleryImage(this, \'gFile\')">Remove</button></div>'
                    );
            });

            if (imgCount > 2) {
                $('#galUpload').css('flex-basis', '100%');
            } else {
                $('#galUpload').css('flex-basis', 'auto');
            }
        });

        // Rules and Regulations preview with file name inside the picture area
        $("#rulesFile").on("change", function(e) {
            const [file] = this.files;
            if (file) {
                $("#rulesPreview img").attr('src', URL.createObjectURL(file));
                $("#rulesPreview").show();
                $("#rulesPreview .file-name-overlay").remove(); // Remove existing overlays
                $("#rulesPreview").append('<p class="file-name-overlay">' + file.name +
                '</p>'); // Display the file name inside the picture area
                $("#rulesPreview .remove-upload").show(); // Show the remove button
            }
        });

        // Requirements preview with file name inside the picture area
        $("#requirementsFile").on("change", function(e) {
            const [file] = this.files;
            if (file) {
                $("#requirementsPreview img").attr('src', URL.createObjectURL(file));
                $("#requirementsPreview").show();
                $("#requirementsPreview .file-name-overlay").remove(); // Remove existing overlays
                $("#requirementsPreview").append('<p class="file-name-overlay">' + file.name +
                '</p>'); // Display the file name inside the picture area
                $("#requirementsPreview .remove-upload").show(); // Show the remove button
            }
        });

        // Qualification preview with file name inside the picture area
        $("#qualificationFile").on("change", function(e) {
            const [file] = this.files;
            if (file) {
                $("#qualificationPreview img").attr('src', URL.createObjectURL(file));
                $("#qualificationPreview").show();
                $("#qualificationPreview .file-name-overlay").remove(); // Remove existing overlays
                $("#qualificationPreview").append('<p class="file-name-overlay">' + file.name +
                '</p>'); // Display the file name inside the picture area
                $("#qualificationPreview .remove-upload").show(); // Show the remove button
            }
        });
    });

    // Remove function for uploaded items and reset input field
    function removeUpload(previewId, inputId) {
        $('#' + previewId).hide(); // Hide the preview
        $('#' + previewId + ' img').attr('src', '{{ asset('images/upload/upload-1.png') }}'); // Reset to default image
        $('#' + previewId + ' p.file-name-overlay').remove(); // Remove the file name overlay
        $('#' + previewId + ' .remove-upload').hide(); // Hide remove button
        $('#' + inputId).val(''); // Clear the file input, allowing it to accept the same file again
    }

    // Remove function for gallery images and reset input field
    function removeGalleryImage(button, inputId) {
        $(button).parent('.gitems').remove(); // Remove the gallery image
        $('#' + inputId).val(''); // Clear the file input for gallery, allowing re-upload
        if ($('.gitems').length === 0) {
            $('#galUpload').addClass('up-load'); // Reset to initial state
        }
    }
    

    document.addEventListener('DOMContentLoaded', function() {
        const rentalNameSelect = document.getElementById('rentalNameSelect');
        const dormitoryFields = document.getElementById('dormitoryFields');

        rentalNameSelect.addEventListener('change', function() {
            const selectedRental = rentalNameSelect.value;
            const dormitoryNames = ['Male Dormitory', 'Female Dormitory', 'International House II'];
            dormitoryFields.style.display = dormitoryNames.includes(selectedRental) ? 'block' : 'none';
        });
    });

    // Add Room Container function
    function addRoomContainer() {
        const roomContainer = document.getElementById('roomContainer');

        // Create a new container for room number and capacity
        const newRoomContainer = document.createElement('div');
        newRoomContainer.classList.add('room-field-container');
        newRoomContainer.innerHTML = `
        <fieldset class="name">
            <div class="body-title mb-10">Room Number <span class="tf-color-1">*</span></div>
            <input type="text" name="room_number[]" placeholder="Enter room number" required>
        </fieldset>

        <fieldset class="name">
            <div class="body-title mb-10">Room Capacity <span class="tf-color-1">*</span></div>
            <input type="number" name="room_capacity[]" placeholder="Enter room capacity" required>
        </fieldset>

        <button type="button" class="remove-room" onclick="removeRoomContainer(this)">Remove Room</button>
    `;
        roomContainer.appendChild(newRoomContainer);
    }

    // Remove Room Container function
    function removeRoomContainer(button) {
        const roomField = button.closest('.room-field-container');
        roomField.remove();
    }

    $(document).ready(function() {
        function toggleStartEndDateFields() {
            const selectedName = $('#rentalNameSelect').val();
            const showDateFields = ['Male Dormitory', 'Female Dormitory', 'International House II'].includes(selectedName);

            if (showDateFields) {
                $('#startEndDateFields').show();
            } else {
                $('#startEndDateFields').hide();
                $('input[name="start_date"]').val(''); // Clear start_date input
                $('input[name="end_date"]').val('');   // Clear end_date input
            }
        }

        // Initial toggle on page load
        toggleStartEndDateFields();

        // Toggle on change
        $('#rentalNameSelect').on('change', function() {
            toggleStartEndDateFields();
        });

        // Validation for start and end dates and room fields
        $('form').on('submit', function(e) {
            const startDate = new Date($('input[name="start_date"]').val());
            const endDate = new Date($('input[name="end_date"]').val());
            const selectedRental = $('#rentalNameSelect').val();
            const dormitoryNames = ['Male Dormitory', 'Female Dormitory', 'International House II'];
            const roomCount = $('#roomContainer .room-field-container').length;

            // Check if Start Date is earlier than End Date
            if (startDate && endDate && startDate >= endDate) {
                e.preventDefault(); // Prevent form submission
                alert('Error: Start Date must be earlier than End Date.');
                $('input[name="end_date"]').focus(); // Set focus on the End Date field
            }

            // Check if rooms are required but none were added
            if (dormitoryNames.includes(selectedRental) && roomCount === 0) {
                e.preventDefault(); // Prevent form submission
                alert('Error: Please add at least one room for this rental.');
                $('#roomContainer').focus();
            }
        });
        $(document).ready(function () {
                // Listen for changes in the Start Date input
                $('#start_date').on('change', function () {
                    const startDate = new Date($(this).val());
                    if (startDate) {
                        // Set the minimum date for the End Date input to the day after the Start Date
                        const minEndDate = new Date(startDate);
                        minEndDate.setDate(minEndDate.getDate() + 1);

                        // Format the date to YYYY-MM-DD
                        const formattedDate = minEndDate.toISOString().split('T')[0];

                        // Update the End Date input
                        $('#end_date').attr('min', formattedDate);
                    }
                });
            });

    });


    $(document).ready(function() {
    // Function to toggle "Add Rental" button visibility
        function toggleAddRentalButton() {
            var selectedName = $('#rentalNameSelect').val();
            var hideRentalButtonNames = ['Male Dormitory', 'Female Dormitory', 'International House II'];

            if (hideRentalButtonNames.includes(selectedName)) {
                $('.tf-button').hide(); // Hide the Add Rental button
            } else {
                $('.tf-button').show(); // Show the Add Rental button
            }
        }

        // Initial toggle on page load
        toggleAddRentalButton();

        // Toggle on change
        $('#rentalNameSelect').on('change', function() {
            toggleAddRentalButton();
        });
    });




</script>

@endpush