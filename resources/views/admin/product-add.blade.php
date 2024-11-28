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

        .stock-status-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stock-status-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stock-status-item label {
            width: 150px;
        }

        .stock-status-item input {
            flex: 1;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Add Product</h3>
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
                        <a href="{{ route('admin.products') }}">
                            <div class="text-tiny">Products</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Add Product</div>
                    </li>
                </ul>
            </div>

            <form class="tf-section-2 form-add-product" method="POST" enctype="multipart/form-data" action="{{ route('admin.product.store') }}" novalidate>
                @csrf

                <div class="wg-box">
                    <fieldset class="name">
                        <div class="body-title mb-10">Product Name <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Enter product name" name="name" tabindex="0"
                            value="{{ old('name') }}" aria-required="true" required="">
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <div class="gap22 cols">
                        <fieldset class="category">
                            <div class="body-title mb-10">Category <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" name="category_id" required>
                                    <option value="">Choose category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @if ($category->children)
                                            @foreach ($category->children as $child)
                                                <option value="{{ $child->id }}">&nbsp; &nbsp; &rarrhk; {{ $child->name }}</option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    @error('category_id')
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

                    <fieldset class="shortdescription">
                        <div class="body-title mb-10">Short Description <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10 ht-150" name="short_description" placeholder="Short Description" tabindex="0"
                            aria-required="true" required="">{{ old('short_description') }}</textarea>
                    </fieldset>
                    @error('short_description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <fieldset class="description">
                        <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10" name="description" placeholder="Description" tabindex="0" aria-required="true"
                            required="">{{ old('description') }}</textarea>
                    </fieldset>
                    @error('description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                </div>

                <div class="wg-box">
                    <fieldset>
                        <div class="body-title">Upload Images <span class="tf-color-1">*</span></div>
                        <div class="upload-image flex-grow">
                            <div class="item" id="imgpreview" style="display:none">
                                <img src="../../../localhost_8000/images/upload/upload-1.png" id="preview-img" class="effect8" alt="">
                                <button type="button" class="remove-upload" onclick="removeUpload('imgpreview', 'myFile')">Remove</button>
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

                    <fieldset>
                        <div class="body-title mb-10">Upload Gallery Images</div>
                        <div class="upload-image mb-16 flex-grow" id="gallery-container">
                            <div id="galUpload" class="item up-load">
                                <label class="uploadfile" for="gFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="text-tiny">Select your images here or click to browse</span>
                                    <input type="file" id="gFile" name="images[]" accept="image/*" multiple="">
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('images') <span class="alert alert-danger text-center">{{$message}} </span> @enderror

                    <!-- **Added Stock Status Fields Above Featured Field** -->
                    <div class="cols gap22">
                        <fieldset class="stock_status">
                            <div class="body-title mb-10">Stock Status <span class="tf-color-1">*</span></div>
                            <div class="stock-status-container">
                                <div class="stock-status-item">
                                    <label for="reorder_quantity">Reorder Quantity:</label>
                                    <input type="number" id="reorder_quantity" name="reorder_quantity" min="0"
                                        value="{{ old('reorder_quantity', 0) }}" required>
                                </div>
                                <div class="stock-status-item">
                                    <label for="outofstock_quantity">Out of Stock Quantity:</label>
                                    <input type="number" id="outofstock_quantity" name="outofstock_quantity" min="0"
                                        value="{{ old('outofstock_quantity', 0) }}" required>
                                </div>
                            </div>
                        </fieldset>
                        @error('instock_quantity')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                        @error('reorder_quantity')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                        @error('outofstock_quantity')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                    </div>
                    <!-- **End of Stock Status Fields** -->

                    <div class="main-fields">
                        <div class="cols gap22">
                            <fieldset class="name">
                                <div class="body-title mb-10">Price <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="text" placeholder="Enter price" name="price"
                                    tabindex="0" value="{{ old('price') }}" aria-required="true">
                            </fieldset>
                            @error('price')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror

                            <fieldset class="name">
                                <div class="body-title mb-10">Quantity <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="text" placeholder="Enter quantity" name="quantity"
                                    tabindex="0" value="{{ old('quantity') }}" aria-required="true">
                            </fieldset>
                            @error('quantity')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror
                        </div>
                    </div>

                    <!-- **Featured Field (Moved Below Stock Status Fields)** -->
                    <div class="cols gap22">
                        <fieldset class="name">
                            <div class="body-title mb-10">Featured</div>
                            <div class="select mb-10">
                                <select class="" name="featured">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('featured')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                    </div>
                    <!-- **End of Featured Field** -->

                    <!-- Submit Button -->
                    <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">Add Product</button>
                    </div>
                </div>

                <div class="wg-box">
                    <div class="gap22 cols">
                        <fieldset class="product-attribute">
                            <div class="body-title mb-10">Product Variant <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" id="product-select">
                                    <option value="">Select Attribute</option>
                                    @foreach ($productAttributes as $attribute)
                                        <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    @error('product_attribute_id')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <div class="cols gap22">
                        <button type="button" id="add-variant-btn" class="btn btn-primary">Add Variant</button>
                    </div>
                </div>

                <div class="wg-box" id="container-box" style="display: none">
                    <div id="variant-fields-container"></div>
                </div>
            </form>
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

            $("#gFile").on("change", function(e) {
                const gphotos = this.files;
                $("#galUpload").removeClass('up-load');
                let imgCount = 0;

                $('#gallery-container .gitems').remove();

                $.each(gphotos, function(key, val) {
                    imgCount++;
                    const fileName = val.name;
                    $('#galUpload').before('<div class="item gitems"><img src="' + URL.createObjectURL(val) +
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
        }

        $(document).ready(function() {
            const addVariantBtn = document.getElementById('add-variant-btn');
            const boxFields = document.getElementById('container-box');
            const mainFields = document.querySelector('.main-fields');
            const variantsContainer = document.getElementById('variant-fields-container');

            function createVariantFields() {
                const selectedAttributeId = $("#product-select").val();
                const selectedAttributeName = $("#product-select option:selected").text();

                if (!selectedAttributeId) {
                    alert("Please select a product attribute before adding a variant.");
                    return;
                }

                const variantCount = variantsContainer.children.length + 1;

                const variantDiv = document.createElement('div');
                variantDiv.className = 'variant-fields';
                variantDiv.setAttribute('data-variant-index', variantCount);
                variantDiv.innerHTML = `
                    <div class="variant-header flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Variant ${variantCount}</h3>
                        <button type="button" class="remove-variant-btn btn btn-danger btn-sm" 
                                data-variant-index="${variantCount}">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                    <div>
                        <input type="hidden" name="product_attribute_id[]" value="${selectedAttributeId}">
                        <fieldset class="name">
                            <div class="body-title mb-10 my-4">Variant for: ${selectedAttributeName}</div>
                            <input type="text" name="variant_name[]" placeholder="Variant Name" required>
                        </fieldset>
                        <fieldset class="name">
                            <div class="body-title mb-10 my-4">Variant Price <span class="tf-color-1">*</span></div>
                            <input class="mb-10" type="text" placeholder="Enter price" name="variant_price[]"
                                tabindex="0" aria-required="true" required>
                        </fieldset>
                        <fieldset class="name">
                            <div class="body-title mb-10 my-4">Variant Quantity:</div>
                            <input type="number" name="variant_quantity[]" placeholder="Variant Quantity" required>
                        </fieldset>
                    </div>
                `;
                variantsContainer.appendChild(variantDiv);

                const removeBtn = variantDiv.querySelector('.remove-variant-btn');
                removeBtn.addEventListener('click', () => {
                    variantsContainer.removeChild(variantDiv);
                    updateMainFieldsVisibility();
                    showContainer();
                    updateVariantNumbers();
                });

                updateMainFieldsVisibility();
                showContainer();
            }

            function updateVariantNumbers() {
                const variants = variantsContainer.querySelectorAll('.variant-fields');
                variants.forEach((variant, index) => {
                    const variantHeader = variant.querySelector('.variant-header h3');
                    variantHeader.textContent = `Variant ${index + 1}`;
                    variant.setAttribute('data-variant-index', index + 1);
                    const removeBtn = variant.querySelector('.remove-variant-btn');
                    removeBtn.setAttribute('data-variant-index', index + 1);
                });
            }

            function showContainer() {
                if (variantsContainer.children.length > 0) {
                    boxFields.style.display = 'block';
                } else {
                    boxFields.style.display = 'none';
                }
            }

            function updateMainFieldsVisibility() {
                if (variantsContainer.children.length > 0) {
                    mainFields.style.display = 'none';
                } else {
                    mainFields.style.display = 'block';
                }
            }

            addVariantBtn.addEventListener('click', createVariantFields);
        });

        function StringToSlug(Text) {
            return Text.toLowerCase()
                .replace(/[^\w ]+/g, "")
                .replace(/ +/g, "-");
        }
    </script>
@endpush
