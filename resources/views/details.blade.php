@extends('layouts.app')
@section('content')
    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();

        // Determine the base home route based on user type
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'DIR' => route('director.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        // Initialize breadcrumbs array with the Home link
        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

        // Handle Shop pages
        if ($currentRoute === 'shop.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
        } elseif ($currentRoute === 'shop.product.details') {
            $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
            $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];

            // Handle About page
        } elseif ($currentRoute === 'about.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];

            // Handle Contact page
        } elseif ($currentRoute === 'contact.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];

            // Add more pages as needed
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp


    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        @if (session('incomplete_profile'))
        <script>
            Swal.fire({
                title: 'Incomplete Profile',
                text: "{{ session('message') }}",
                icon: 'warning',
                confirmButtonText: 'Complete Profile'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('user.profile') }}";
                }
            });
        </script>
         @endif


        <div class="mb-md-1 pb-md-3"></div>
        <section class="product-single container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="product-gallery">
                        <div class="gallery-wrapper">
                            <!-- Thumbnails -->
                            <div class="thumbnails">
                                <div class="swiper-container thumbnail-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="thumbnail-img"
                                                src="{{ asset('uploads/products/thumbnails') }}/{{ $product->image }}"
                                                alt="{{ $product->name }}" height="204">
                                        </div>
                                        @foreach (explode(',', $product->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="thumbnail-img"
                                                    src="{{ asset('uploads/products/thumbnails') }}/{{ $gimg }}"
                                                    alt="{{ $product->name }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Main Image -->
                            <div class="main-image">
                                <div class="swiper-container main-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="h-auto main-img"
                                                src="{{ asset('uploads/products') }}/{{ $product->image }}"
                                                alt="{{ $product->name }}">
                                            <a data-fancybox="gallery"
                                                href="{{ asset('uploads/products') }}/{{ $product->image }}"
                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                title="{{ $product->name }}"></a>
                                        </div>
                                        @foreach (explode(',', $product->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="h-auto main-img"
                                                    src="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                    alt="{{ $product->name }}">
                                                <a data-fancybox="gallery"
                                                    href="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    title="{{ $product->name }}"></a>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Navigation buttons -->
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-5">

                    <h1 class="product-single__name">{{ $product->name }}</h1>
                    <div class="product-single__price">
                        <span class="current-price" id="current-price">&#8369;
                            @if ($product->attributeValues->isNotEmpty() && isset($product->attributeValues[0]->price))
                                {{ number_format($product->attributeValues[0]->price, 2) }}
                            @else
                                {{ number_format($product->price, 2) }}
                            @endif
                        </span>
                    </div>

                    <div class="product-single__short-desc ">
                        <p> {{ $product->short_description }}</p>
                    </div>

                    <div class="product-single__description">
                        {{ $product->description }}
                    </div>

                    <div class="variant-container mb-3 product-single__meta-info py-3">

                        <div class="d-flex flex-column">
                            @foreach ($uniqueAttributes as $attributeId => $attribute)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="product-attributes me-3 mx-2">
                                        <strong>{{ $attribute->name }}:</strong>
                                    </div>
                                    <div class="product-attribute-values d-flex">
                                        @if (isset($groupedAttributes[$attributeId]))
                                            @foreach ($groupedAttributes[$attributeId] as $variant)
                                                <button type="button" class="btn btn-outline-primary variant-button me-2"
                                                    data-attribute-id="{{ $attributeId }}"
                                                    data-variant-id="{{ $variant->id }}"
                                                    data-variant-price="{{ $variant->price }}"
                                                    data-variant-quantity="{{ $variant->quantity }}">
                                                    {{ $variant->value }}
                                                </button>
                                            @endforeach
                                        @else
                                            <span>No values available</span>
                                        @endif
                                    </div>

                                    {{-- Hidden input to store the selected attribute value --}}
                                    <input type="hidden" name="attribute[{{ $attributeId }}]"
                                        id="attribute-{{ $attributeId }}" value="">
                                </div>
                            @endforeach
                            {{-- @error('variant_id')
                                <div class="alert alert-danger mt-2" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror --}}
                            <div id="error-message" class="mt-2 p-3 alert alert-danger" role="alert"
                                style="display: none;">
                                Please select a variant.
                            </div>
                        </div>
                    </div>



                    <div class="product-single__meta-info">
                        <div class="meta-item">
                            <label>Categories:</label>
                            <span>{{ $product->category->name }}</span>
                        </div>
                    </div>

                    
                        <form id="add-to-cart-form" action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <div class="product-single__addtocart">
                                <div class="qty-control position-relative">
                                    <input type="number" name="quantity" value="1" min="1"
                                        class="qty-control__number text-center">
                                    <div class="qty-control__reduce">-</div>
                                    <div class="qty-control__increase">+</div>
                                </div>
                                <div class="meta-item">
                                    <span style="font-size: 14px" id="available-quantity">
                                        @if ($product->attributeValues->isNotEmpty() && isset($product->attributeValues[0]->quantity))
                                            {{ $product->attributeValues[0]->quantity > 0 ? $product->attributeValues[0]->quantity : 'Out of Stock' }}
                                        @else
                                            {{ $product->quantity > 0 ? $product->quantity  : 'Out of Stock' }}
                                        @endif
                                    </span>
                                </div>
                                <input type="hidden" name="id" value="{{ $product->id }}" />
                                <input type="hidden" name="name" value="{{ $product->name }}" />
                                <input type="hidden" name="price" id="selected-price" value="{{ $product->price }}" />
                                <input type="hidden" name="variant_id" id="selected-variant-id" value="" />
                                <button type="submit" class="btn btn-shop btn-addtocart" id="add-to-cart-button">
                                    Add to Cart
                                </button>
                            </div>
                        </form>
                  


                </div>
            </div>


        </section>
    </main>

    <hr class="mt-5 text-secondary" />

    <section id="collection" class="py-3 pt-5">
        <div class="container pb-5">
            <div class="title-h2 text-center">
                <h2 class="position-relative d-inline-block">Related Products</h2>
            </div>

            <div class="row g-4 my-5 mx-auto owl-carousel owl-theme">
                @foreach ($rproducts as $rproduct)
                    <div class="col product-item d-flex flex-column h-100">
                        <div class="product-img h-100">
                            <!-- Swiper Slider for Product Images -->
                            <div class="swiper-container js-swiper-slider">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <a
                                            href="{{ route('shop.product.details', ['product_slug' => $rproduct->slug]) }}">
                                            <img loading="lazy"
                                                src="{{ asset('uploads/products') }}/{{ $rproduct->image }}"
                                                alt="{{ $rproduct->name }}" class="img-fluid d-block mx-auto"
                                                style="object-fit: cover; aspect-ratio: 1/1;">
                                        </a>
                                    </div>
                                    @foreach (explode(',', $rproduct->images) as $gimg)
                                        <div class="swiper-slide">
                                            <img loading="lazy"
                                                src="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                alt="{{ $rproduct->name }}" class="img-fluid d-block mx-auto"
                                                style="object-fit: cover; aspect-ratio: 1/1;">
                                        </div>
                                    @endforeach
                                  
                                    <div class="swiper-slide">
                                        <a
                                            href="{{ route('shop.product.details', ['product_slug' => $rproduct->slug]) }}">
                                            <img loading="lazy"
                                                src="{{ asset('uploads/products') }}/{{ $rproduct->image }}"
                                                alt="{{ $rproduct->name }}" class="img-fluid d-block mx-auto"
                                                style="object-fit: cover; aspect-ratio: 1/1;">
                                        </a>
                                    </div>
                                    @foreach (explode(',', $rproduct->images) as $gimg)
                                        <div class="swiper-slide">
                                            <img loading="lazy"
                                                src="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                alt="{{ $rproduct->name }}" class="img-fluid d-block mx-auto"
                                                style="object-fit: cover; aspect-ratio: 1/1;">
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Pagination -->
                                <div class="swiper-pagination slideshow-pagination"></div>
                                <!-- Navigation Buttons -->
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>

                        </div>
                        <div class="product-info px-3 py-3"
                            onclick="window.location.href='{{ route('shop.product.details', ['product_slug' => $rproduct->slug]) }}'">
                            <h1 class="title-cards fw-normal text-capitalize mb-2 display-2 ">{{ $rproduct->name }}</h1>
                            <p class="span-description fw-light mb-2">{{ $rproduct->short_description }}</p>
                            <div class="avail d-flex justify-content-center">
                                <span class="availability fw-bold me-2">Available items: </span>
                                <span class="avail-tags"
                                    style="color: 
                                    @if ($rproduct->attributeValues->isNotEmpty() && isset($rproduct->attributeValues[0]->quantity)) {{ $rproduct->attributeValues[0]->quantity > 0 ? ($rproduct->attributeValues[0]->quantity == 1 ? 'orange' : 'green') : 'red' }}
                                    @else
                                        {{ $rproduct->quantity > 0 ? ($rproduct->quantity == 1 ? 'orange' : 'green') : 'red' }} @endif
                                ;">
                                    @if ($rproduct->attributeValues->isNotEmpty() && isset($rproduct->attributeValues[0]->quantity))
                                        {{ $rproduct->attributeValues[0]->quantity > 0 ? $rproduct->attributeValues[0]->quantity : 0 }}
                                    @else
                                        {{ $rproduct->quantity > 0 ? $rproduct->quantity : 0 }}
                                    @endif
                                </span>
                            </div>

                            <h2 class="product-type text-primary pt-2">
                                &#8369;
                                @if ($rproduct->attributeValues->isNotEmpty() && isset($rproduct->attributeValues[0]->price))
                                    {{ number_format($rproduct->attributeValues[0]->price, 2) }}
                                @else
                                    {{ number_format($rproduct->price, 2) }}
                                @endif
                            </h2>


                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
       document.addEventListener('DOMContentLoaded', function() {
    const mainSwiper = new Swiper('.main-swiper', {
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        loop: true,
        on: {
            slideChange: function() {
                const activeIndex = this.realIndex;
                document.querySelectorAll('.thumbnail-img').forEach((thumbnail, index) => {
                    thumbnail.classList.toggle('active', index === activeIndex);
                });
            }
        }
    });

    const thumbnailSwiper = new Swiper('.thumbnail-swiper', {
        direction: 'vertical',
        slidesPerView: 'auto',
        spaceBetween: 10,
    });
    
    document.querySelectorAll('.thumbnail-img').forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', function() {
            mainSwiper.slideToLoop(index);
            document.querySelectorAll('.thumbnail-img').forEach((thumb) => thumb.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Initialize the first thumbnail as active
    document.querySelector('.thumbnail-img').classList.add('active');
    const qtyInputs = document.querySelectorAll('.qty-control__number');
    const decreaseBtns = document.querySelectorAll('.qty-control__reduce');
    const increaseBtns = document.querySelectorAll('.qty-control__increase');
    const availableQuantitySpan = document.getElementById('available-quantity');
    let maxAvailableQty = parseInt(availableQuantitySpan.textContent) || 0;

    // Handle decrease button clicks
    decreaseBtns.forEach((decreaseBtn, index) => {
        decreaseBtn.addEventListener('click', function() {
            const qtyInput = qtyInputs[index];
            if (qtyInput.value > 1) {
                qtyInput.value--;
            } else {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to remove this item from your cart.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(`remove-item-form-${index}`);
                        form.submit();
                    }
                });
            }
        });
    });

    // Handle increase button clicks
    increaseBtns.forEach((increaseBtn, index) => {
        increaseBtn.addEventListener('click', function() {
            const qtyInput = qtyInputs[index];
            let currentQty = parseInt(qtyInput.value);
            
            if (currentQty < maxAvailableQty) {
                qtyInput.value++;
            } else {
                Swal.fire({
                    title: 'Maximum quantity reached!',
                    text: `Only ${maxAvailableQty} items are available for this product.`,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    const addToCartButton = document.getElementById('add-to-cart-button');
    const errorMessage = document.getElementById('error-message');
    const selectedVariantIdInput = document.getElementById('selected-variant-id');
    const variantButtons = document.querySelectorAll('.variant-button');
    const hasVariants = variantButtons.length > 0;
    let activeButton = null;

    if (!addToCartButton) return;

    const variantData = {};
    variantButtons.forEach(button => {
        variantData[button.dataset.variantId] = {
            quantity: parseInt(button.dataset.variantQuantity) || 0,
            price: parseFloat(button.dataset.variantPrice) || 0
        };
    });

    addToCartButton.addEventListener('click', function(event) {
        event.preventDefault();

        if (hasVariants && !selectedVariantIdInput.value) {
            errorMessage.style.display = 'block';
            return;
        } else {
            errorMessage.style.display = 'none';
        }

        const formData = new FormData(document.getElementById('add-to-cart-form'));

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('cart.add') }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 401) {
                window.location.href = "{{ route('login') }}";
            } else if (xhr.status === 403) {
                Swal.fire({
                    title: 'Error!',
                    text: JSON.parse(xhr.responseText).message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else if (xhr.status === 200) {
                Swal.fire({
                    title: 'Added to Cart!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = "{{ route('cart.index') }}";
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'There was an issue adding the product to your cart.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }
    };


        xhr.send(formData);
    });

    if (hasVariants) {
        variantButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (activeButton) {
                    activeButton.classList.remove('btn-primary');
                    activeButton.classList.add('btn-outline-primary');
                }

                const variantId = this.dataset.variantId;
                selectedVariantIdInput.value = variantId;
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                activeButton = this;

                if (availableQuantitySpan && variantData[variantId]) {
                    maxAvailableQty = variantData[variantId].quantity;
                    availableQuantitySpan.textContent = maxAvailableQty > 0 ?
                        `${maxAvailableQty} pieces available` :
                        'Out of Stock';
                    addToCartButton.disabled = maxAvailableQty <= 0;
                }

                errorMessage.style.display = 'none';
            });
        });
    }
});

    </script>
@endpush
