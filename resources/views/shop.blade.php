        @extends('layouts.app')
        @section('content')
            <!-- Custom CSS -->
            <style>
                .hover-bg-light:hover {
                    background-color: #f8f9fa;

                }

                .product-info {
                    cursor: pointer;
                }
            </style>
            @php
                $user = auth()->user();
                $currentRoute = request()->route()->getName();

                // Determine the base home route based on user type
                $homeRoute = match ($user->utype ?? 'guest') {
                    'USR' => route('user.index'),
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
                <div class="row">
                    <!-- Sidebar -->
                    <div class="col-md-3">
                        <div class="filter-sidebar bg-white rounded-3 p-4">
                            <div class="accordion" id="categories-list">
                                <div class="accordion-item border-0 mb-3 rounded-3">
                                    <h5 class="accordion-header" id="accordion-heading-1">
                                        <button class="accordion-button py-3 border-0 text-uppercase fw-bold text-primary"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#accordion-filter-1"
                                            aria-expanded="true" aria-controls="accordion-filter-1">
                                            Product Categories
                                        </button>
                                    </h5>
                                    <div id="accordion-filter-1" class="accordion-collapse collapse show"
                                        aria-labelledby="accordion-heading-1" data-bs-parent="#categories-list">
                                        <div class="accordion-body p-0">
                                            <ul class="list-unstyled mb-0">
                                                @foreach ($categories as $category)
                                                    <li
                                                        class="d-flex justify-content-between align-items-center py-2 border-bottom hover-bg-light">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="categories" value="{{ $category->id }}"
                                                                @if (in_array($category->id, explode(',', $f_categories))) checked @endif
                                                                aria-label="Select {{ $category->name }}">
                                                            <label class="form-check-label ms-2">
                                                                {{ $category->name }}
                                                            </label>
                                                        </div>
                                                        <span
                                                            class="badge bg-secondary rounded-pill">{{ $category->products->count() }}</span>
                                                    </li>

                                                    @if ($category->children->isNotEmpty())
                                                        @foreach ($category->children as $subcategory)
                                                            <li
                                                                class="d-flex justify-content-between align-items-center py-2 border-bottom hover-bg-light ms-4">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="categories" value="{{ $subcategory->id }}"
                                                                        @if (in_array($subcategory->id, explode(',', $f_categories))) checked @endif
                                                                        aria-label="Select {{ $subcategory->name }}">
                                                                    <label class="form-check-label ms-2">
                                                                        &nbsp;&rarrhk; {{ $subcategory->name }}
                                                                    </label>
                                                                </div>
                                                                <span
                                                                    class="badge bg-secondary rounded-pill">{{ $subcategory->products->count() }}</span>
                                                            </li>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gender Accordion -->
                            <div class="accordion" id="gender-filter">
                                <div class="accordion-item border-0 mb-3 rounded-3">
                                    <h5 class="accordion-header" id="accordion-gender-heading">
                                        <button class="accordion-button py-3 border-0 text-uppercase fw-bold text-primary"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#accordion-gender-filter" aria-expanded="true"
                                            aria-controls="accordion-gender-filter">
                                            Product Type
                                        </button>
                                    </h5>
                                    <div id="accordion-gender-filter" class="accordion-collapse collapse show"
                                        aria-labelledby="accordion-gender-heading" data-bs-parent="#gender-filter">
                                        <div class="accordion-body p-0">
                                            <ul class="list-unstyled mb-0">
                                                <li
                                                    class="d-flex justify-content-between align-items-center py-2 border-bottom hover-bg-light">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="sex"
                                                            id="sex-all" value=""
                                                            {{ request('sex') == '' ? 'checked' : '' }}
                                                            aria-label="Select all genders">
                                                        <label class="form-check-label ms-2" for="sex-all">
                                                            All
                                                        </label>
                                                    </div>
                                                </li>
                                                <li
                                                    class="d-flex justify-content-between align-items-center py-2 border-bottom hover-bg-light">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="sex"
                                                            id="sex-male" value="male"
                                                            {{ request('sex') == 'male' ? 'checked' : '' }}
                                                            aria-label="Select male">
                                                        <label class="form-check-label ms-2" for="sex-male">
                                                            Men
                                                        </label>
                                                    </div>
                                                </li>
                                                <li
                                                    class="d-flex justify-content-between align-items-center py-2 border-bottom hover-bg-light">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="sex"
                                                            id="sex-female" value="female"
                                                            {{ request('sex') == 'female' ? 'checked' : '' }}
                                                            aria-label="Select female">
                                                        <label class="form-check-label ms-2" for="sex-female">
                                                            Women
                                                        </label>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>




                    <!-- Main Content -->
                    <div class="col-md-9">
                        <div class="d-flex justify-content-end mb-4">
                            <div class="d-flex align-items-flex">
                                <select class="shop-acs__select form-select w-auto border-0 py-0 order-1 order-md-0"
                                    aria-label="Sort Items" name="orderBy" id="orderBy">
                                    <option value="-1" {{ $order == -1 ? 'selected' : '' }}>Default Sorting</option>
                                    <option value="1" {{ $order == 1 ? 'selected' : '' }}>Date, New to Old</option>
                                    <option value="2" {{ $order == 2 ? 'selected' : '' }}>Date, Old to New</option>
                                    <option value="3" {{ $order == 3 ? 'selected' : '' }}>Price, Low to High</option>
                                    <option value="4" {{ $order == 4 ? 'selected' : '' }}>Price, High to Low</option>
                                </select>
                            </div>
                        </div>
                        <div id="product-list">
                            @include('partials.products-list', ['products' => $products])
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
                </div>
                </div>
            </main>

            <form action="{{ route('shop.index') }}" method="get" id="frmfilter">
                <input type="hidden" name="page" value="{{ $products->currentPage() }}">
                <input type="hidden" name="order" id="order" value="{{ $order }}" />
                <input type="hidden" name="categories" id="hdnCategories" />
                <input type="hidden" name="sex" id="hdnSex" value="{{ request('sex') }}" />

            </form>
        @endsection

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var swiper = new Swiper('.js-swiper-slider', {
                        slidesPerView: 1,
                        effect: 'fade',
                        loop: true,
                        pagination: {
                            el: '.slideshow-pagination',
                            type: 'bullets',
                            clickable: true,
                        },
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                    });

                    @if (session('success'))
                        toastr.success('{{ session('success') }}');
                    @endif

                    @if (session('error'))
                        toastr.error('{{ session('error') }}');
                    @endif
                });

                $(function() {
                    function submitFilterForm() {
                        $.ajax({
                            url: $('#frmfilter').attr('action'), // Get the form action URL
                            type: 'GET', // Use GET method
                            data: $('#frmfilter').serialize(), // Serialize the form data
                            success: function(data) {
                                $('#product-list').html(data); // Update the product list
                            },
                            error: function(xhr) {
                                console.error("An error occurred: " + xhr.status + " " + xhr.statusText);
                                toastr.error('An error occurred while loading products.');
                            }
                        });
                    }

                    // Handle order change
                    $("#orderBy").on("change", function() {
                        $('#order').val($(this).val());
                        submitFilterForm(); // Submit via AJAX
                    });

                    // Handle categories change
                    $("input[name='categories']").on("change", function() {
                        var categories = [];
                        $("input[name='categories']:checked").each(function() {
                            categories.push($(this).val());
                        });
                        $("#hdnCategories").val(categories.join(",")); // Update hidden input
                        submitFilterForm(); // Submit via AJAX
                    });

                    // Handle gender radio button change
                    $("input[name='sex']").on("change", function() {
                        $("#hdnSex").val($(this).val());
                        submitFilterForm(); // Submit via AJAX
                    });
                });
            </script>
        @endpush
