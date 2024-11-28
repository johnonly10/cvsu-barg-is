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
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(20, 19, 20, 0.2);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
        <div class="main-content-inner">
            <!-- main-content-wrap -->
            <div class="main-content-wrap">
                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                    <h3>Category infomation</h3>
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
                            <a href="{{ route('admin.categories') }}">
                                <div class="text-tiny">Categories</div>
                            </a>
                        </li>
                        <li>
                            <i class="icon-chevron-right"></i>
                        </li>
                        <li>
                            <div class="text-tiny">New Category</div>
                        </li>
                    </ul>
                </div>
                <!-- new-category -->
                <div class="wg-box">
                    <form class="form-new-product form-style-1" action="{{ route('admin.category.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <fieldset class="name">
                            <div class="body-title">Category Name <span class="tf-color-1">*</span></div>
                            <input class="flex-grow" type="text" placeholder="Category name" name="name" tabindex="0"
                                value="{{ old('name') }}" aria-required="true" required="">
                        </fieldset>
                        @error('name')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror

            
                        <fieldset class="name">
                            <div class="body-title">Parent Category<span class="tf-color-2"> (optional)</span></div>
                            <div class="select w-100">
                                <select name="parent_id" class="d-block">
                                    <option value="">Select Parent Category</option>
                                    @foreach($parentCategories as $parentCategory)
                                        <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                        

                        <fieldset>
                            <div class="body-title">Upload images <span class="tf-color-1">*</span></div>
                            <div class="upload-image flex-grow">
                                <div class="item" id="imgpreview" style="display:none">
                                    <img src="../../../localhost_8000/images/upload/upload-1.png" id="preview-img" class="effect8" alt="">
                                    <button type="button" class="remove-upload" onclick="removeUpload('imgpreview')">Remove</button>
                                </div>
                                <div id="upload-file" class="item up-load">
                                    <label class="uploadfile" for="myFile">
                                        <span class="icon">
                                            <i class="icon-upload-cloud"></i>
                                        </span>
                                        <span class="body-text">Select your images here or click to browse</span>
                                        <input type="file" id="myFile" name="image" accept="image/*" required>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                        @error('image') <span class="alert alert-danger text-center">{{$message}} </span> @enderror

                        
                        <div class="bot">
                            <div></div>
                            <button class="tf-button w208" type="submit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
             $(function() {
            $("#myFile").on("change", function(e) {
                const [file] = this.files;
                if (file) {
                    $("#imgpreview img").attr('src', URL.createObjectURL(file));
                    $("#imgpreview").show();
                    $("#imgpreview .remove-upload").show(); 
                }
            });


            $("input[name='name']").on("input", function() {
                $("input[name='slug']").val(StringToSlug($(this).val()));
            });

        });

        function removeUpload(previewId, inputId) {
            $('#' + previewId).hide();
            $('#' + previewId + ' img').attr('src', '');
            $('#' + previewId + ' p.file-name-overlay').remove();
            $('#' + previewId + ' button').off();
            $('#' + previewId + ' button').css('display', 'none');
            $('#' + inputId).val('');
        }


            function removeGalleryImage(button, inputId) {
                $(button).parent('.gitems').remove();
                $('#' + inputId).val('');
                if ($('.gitems').length === 0) {
                    $('#galUpload').addClass('up-load');
                }

                $("#myFile").on("change", function(e) {
                const [file] = this.files;
                if (file) {
                    $("#imgpreview img").attr('src', URL.createObjectURL(file));
                    $("#imgpreview").show();
                    $("#imgpreview").append('<p class="file-name-overlay">' + file.name + '</p>'); 
                    $("#imgpreview .remove-upload").show(); 
                }
            });
        }

        function StringToSlug(Text) {
            return Text.toLowerCase()
                .replace(/[^\w ]+/g, "")
                .replace(/ +/g, "-");
        }

        function previewImage(event) {
            const file = event.target.files[0];
            const imgPreview = document.getElementById('imgpreview');
            const previewImg = document.getElementById('preview-img');
            const removeBtn = document.getElementById('remove-btn');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imgPreview.style.display = 'block';
                    removeBtn.style.display = 'inline-block';
                }
                reader.readAsDataURL(file);
            }
        }

        document.getElementById('remove-btn').addEventListener('click', function() {
            const imgPreview = document.getElementById('imgpreview');
            const previewImg = document.getElementById('preview-img');
            const removeBtn = document.getElementById('remove-btn');
            const fileInput = document.getElementById('myFile');

          
            imgPreview.style.display = 'none';
            previewImg.src = '';
            fileInput.value = ''; 
            removeBtn.style.display = 'none'; 
        });

        </script>
    @endpush
