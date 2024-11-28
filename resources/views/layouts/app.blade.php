<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Information System') }}</title>
    <meta http-equiv="content-type" content="text/html; charset-utf-8" />
    <meta name="author" content="barg unit" />
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('../images/favicon-logo.png') }}" type="image/x-icon">
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


    <!-- Bootstrap CDN-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/pikaday.min.js"></script>
    <link rel="stylesheet"
        type="text/css"href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/css/pikaday.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.13.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- Sweet Alert JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11.13.1/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="{{ asset('./font-awesome-6-pro-main/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('owl_carousel/owl.theme.default.css') }}">
    <link rel="stylesheet" href="{{ asset('owl_carousel/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('css/plugins/swiper.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('plugins/ijaboCropTool/ijaboCropTool.min.css') }}">
   


    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @stack('styles')

</head>

<body data-bs-spy="scroll" data-bs-target="#goTop">
    <style>
            /* Product Item Styling */
      
         #box-content-search li{
            list-style: none;
         }
         #box-content-search  .product-item{
            margin-bottom: 2px;
         }

         .divider {
        border-top: 1px solid #eaeaea; 
        margin: 0; 
        padding: 0; 
        width: 100%; 
        height: 1px; 
    }
    </style>


<nav class="navbar navbar-expand-lg navbar-light bg-white py-4 fixed-top">
  <div class="container">
      <!-- Logo and Brand -->
      <a href="{{ route('home.index') }}" class="navbar-brand d-flex justify-content-between align-items-center order-lg-0">
          <img src="{{ asset('images/logo.png') }}" alt="Site Logo" class="navbar-logo">
      </a>

      <!-- Search and Cart Buttons -->
      <div class="order-lg-2 nav-btns header-tools__item header-tools__cart">
          <div class="header-tools d-flex align-items-center">
            
              <div class="header-tools__item hover-container">
                  <a class="js-search-popup search-field__actor" href="#" id="searchToggle">
                      <i class="fa-regular fa-magnifying-glass" id="searchIcon"></i>
                      <i class="fa-solid fa-times d-none" id="closeIcon"></i>
                  </a>
              </div>

              <button type="button" class="btn position-relative" onclick="window.location.href='{{ route('cart.index') }}'">
                  <i class="fa-sharp fa-regular fa-bag-shopping"></i>
                  @if (Cart::instance('cart')->content()->count() > 0)
                      <span class="cart-amount d-block position-absolute js-cart-items-count">{{ Cart::instance('cart')->content()->count() }}</span>
                  @endif
              </button>
              @guest
                  <button type="button" class="btn position-relative" onclick="location.href='{{ route('login') }}'">
                      <i class="fa-regular fa-user"></i>
                  </button>
                    @else
            <!-- Profile Button -->
                    <button type="button" class="btn d-flex align-items-center position-relative" 
                    onclick="location.href='{{ Auth::user()->utype == 'ADM' ? route('admin.index') : (Auth::user()->utype == 'DIR' ? route('admin.index') : route('user.index')) }}'">
                <div class="profile-image d-flex justify-content-center me-2">
                    @if(Auth::user()->profile_image)
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image" class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <img src="{{ asset('images/profile.jpg') }}" loading="lazy" alt="Default Profile Image" class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    @endif
                </div>
                <span class="d-none d-md-inline">{{ strtok(Auth::user()->name, ' ') }}</span>
            </button>
    
                  {{-- <!-- Profile Dropdown -->
                  <div class="dropdown">
                      <button class="btn dropdown-toggle d-flex align-items-center" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                          <div class="profile-image d-flex justify-content-center me-2">
                              @if(Auth::user()->profile_image)
                                  <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image" class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                              @else
                                  <img src="{{ asset('images/profile.jpg') }}" loading="lazy" alt="Default Profile Image" class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                              @endif
                          </div>
                          <span class="d-none d-md-inline">{{ strtok(Auth::user()->name, ' ') }}</span>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                          <li><a class="dropdown-item" href="{{ Auth::user()->utype == 'ADM' ? route('admin.index') : route('user.index') }}">Profile</a></li>
                          <li><a class="dropdown-item" href="#">Settings</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                              <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                  @csrf
                                  <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                              </form>
                          </li>
                      </ul>
                  </div> --}}
              @endguest

          </div>
      </div>

      <!-- Navbar Menu -->
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse order-lg-1" id="navMenu">
          <ul class="navbar-nav mx-auto text-center">
              <li class="nav-item px-2 py-2">
                  <a href="{{ route('home.index') }}" class="nav-link text-uppercase text-dark">Home</a>
              </li>
              <li class="nav-item px-2 py-2">
                  <a href="{{ route('shop.index') }}" class="nav-link text-uppercase text-dark">Shop</a>
              </li>
              <li class="nav-item px-2 py-2">
                  <a href="{{ route('rentals.index') }}" class="nav-link text-uppercase text-dark">Rentals</a>
              </li>
              <li class="nav-item px-2 py-2">
                <a href="{{ route('about.index') }}" class="nav-link text-uppercase text-dark">About Us</a>
              </li>
              <li class="nav-item px-2 py-2 border-0">
                  <a href="{{ route('home.contact') }}" class="nav-link text-uppercase text-dark">Contact</a>
              </li>
          </ul>
      </div>
  </div>

  <!-- Search Popup -->
  <div class="search-popup js-hidden-content">
      <form action="#" method="GET" class="search-field container">
          <p class="text-uppercase text-secondary fw-medium mb-4">What are you looking for?</p>
          <div class="position-relative">
              <input class="search-field__input search-popup__input w-100 fw-medium" id="search-input" type="text" name="search-keyword" placeholder="Search products" />
              <button class="search-popup__submit" type="submit">
                  <i class="fa-solid fa-magnifying-glass"></i>
              </button>
          </div>
          <div class="search-popup__results">
              <ul id="box-content-search"></ul>
          </div>
      </form>
  </div>
</nav>


    


    <!-- go top  -->
    <div id="goTop">
        <a href="#topbar" id="goTopbtn"><i class="fa-solid fa-chevron-up"></i></a>
    </div>


    @yield('content')


    <!-- End of section -->
    <footer>
        <!-- Footer Section -->
        <div class="footer-section footer-top-copyright">
            <div class="container">
                <div class="row g-5 mb-5">
                    <div class="col-lg-4">
                        <div class="footer-logo-wrap mb-4">
                            <a href="#" class="footer-logo text-capitalize">BaRG<span>.</span></a>
                        </div>
                        <p class="mb-4">
                            The Business and Resource Generation (BaRG) Unit has been a cornerstone of our campus
                            community for years, offering a wide range of resources, items, and rentals. From uniforms
                            and books to souvenirs and essential supplies, BaRG Unit is your go-to marketplace for all
                            campus needs.
                        </p>
                        <ul class="list-unstyled custom-social">
                            <li>
                                <a href="https://www.facebook.com/obarg.cvsu/">
                                    <span class="fa fa-brands fa-facebook-f"></span>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.youtube.com/channel/UC6p5srphejusQzaulrxmtow">
                                    <span class="fa fa-brands fa-youtube"></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-8">
                        <div class="row links-wrap">
                            <div class="col-6 col-sm-6 col-md-3">
                                <ul class="list-unstyled">
                                    <li class="fw-bold">About Us</li>
                                    <li><a class="text-muted" href="#">Values</a></li>
                                    <li><a class="text-muted" href="#">Our Team</a></li>
                                    <li><a class="text-muted" href="#">History</a></li>
                                    <li><a class="text-muted" href="#">Services</a></li>
                                </ul>
                            </div>
                            <div class="col-6 col-sm-6 col-md-3">
                                <ul class="list-unstyled">
                                    <li class="fw-bold">Contact Us</li>
                                    <li><a class="text-muted" href="#">Cavite State University - Don Severino
                                            Delas Alas Campus, Indang, Cavite</a></li>
                                    <li><a class="text-muted" href="#">barg@gmail.com</a></li>
                                    <li><a class="text-muted" href="#">5VXJ+4H Indang, Cavite</a></li>
                                    <li><a class="text-muted" href="#">+639</a></li>
                                </ul>
                            </div>
                            <div class="col-6 col-sm-6 col-md-3">
                                <ul class="list-unstyled">
                                    <li class="fw-bold">Links</li>
                                    <li><a class="text-muted" href="#">Product</a></li>
                                    <li><a class="text-muted" href="#">Rental</a></li>
                                    <li><a class="text-muted" href="#about">About Us</a></li>
                                    <li><a class="text-muted" href="#">Contacts</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top copyright">
                    <div class="row pt-4">
                        <div class="col-lg-6 text-center text-lg-start mb-2 mb-lg-0">
                            <p>
                                &copy;
                                <script>
                                    document.write(new Date().getFullYear());
                                </script> All Rights Reserved.
                            </p>
                        </div>
                        <div class="col-lg-6 text-center text-lg-end">
                            <ul class="list-unstyled d-inline-flex ms-auto">
                                <li class="me-4"><a href="#">Terms &amp; Conditions</a></li>
                                <li><a href="#">Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </footer>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
    <script src = "{{ asset('js/plugins/swiper.min.js') }}" ></script>
    <script src="{{ asset('plugins/ijaboCropTool/ijaboCropTool.min.js') }}"></script>
    <!-- owl carousel script  -->
    <script src="{{ asset('owl_carousel/owl.carousel.js') }}"></script>
    <!-- custom owljs script  -->
    <script src="{{ asset('js/index.js') }}"></script>
    <!-- countdown js  -->
    <script src="{{ asset('js/countdown.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>
  

    <script>
    $(document).ready(function() {
    $("#search-input").on("keyup", function() {
      var searchQuery = $(this).val().trim(); // Trim whitespace from the query
      
      if (searchQuery.length > 2) { // Proceed only if query length is greater than 2
        $.ajax({
          type: "GET",
          url: "{{ route('home.search') }}", // Route to search endpoint
          data: { query: searchQuery }, // Correct data parameter format
          dataType: 'json', // Expected data type from server
          success: function(data) {
            $("#box-content-search").empty(); // Clear previous results
            
            if (data.length > 0) {
              $.each(data, function(index, item) {
                // Generate the URL with the item's slug
                var urlTemplate = "{{ route('shop.product.details', ['product_slug' => 'product_slug_pls']) }}";
                var productUrl = urlTemplate.replace('product_slug_pls', item.slug);
                
                // Append the search result item
                $("#box-content-search").append(
                  `<li>
                     <ul>
                       <li class="product-item-search gap14 mb-10">
                         <div class="image no-bg">
                           <img src="{{ asset('uploads/products/thumbnails/') }}/${item.image}" alt="${item.name}">
                         </div>
                         <div class="flex items-center justify-between gap20 flex-grow">
                           <div class="name">
                             <a href="${productUrl}" class="body-text">${item.name}</a>
                           </div>
                         </div>
                       </li>
                       <li class="mb-10">
                         <div class="divider"></div>
                       </li>
                     </ul>
                   </li>`
                );
              });
            } else {
              $("#box-content-search").html('<p>No results found</p>');
            }
          },
          error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
          }
        });
      } else {
        $("#box-content-search").empty();
      }
    });
  });

  $(function () {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>

    <!-- main js  -->
    <script src="{{ asset('js/main.js') }}"></script>
    @stack('scripts')


</body>

</html>
