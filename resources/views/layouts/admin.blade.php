<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <title>{{ config('app.name', 'Information System') }}</title> --}}
    <title>{{ isset($pageTitle) ? $pageTitle : config('app.name', 'Information System') }}</title>

    <meta http-equiv="content-type" content="text/html; charset-utf-8" />
    <meta name="author" content="barg unit" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/animate.min.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/animation.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap-select.min.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }} ">
    <link rel="stylesheet" href="{{ asset('assets/font/fonts.css') }} ">
    <link rel="stylesheet" href="{{ asset('assets/icon/style.css') }} ">
    {{-- <link rel="stylesheet" type="text/css" href="{{asset('css/sweetalert.min.css')}}"> --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }} ">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000",
            "extendedTimeOut": "0"
        };

        const notificationCount = $('.notification-count');
        const notificationList = $('#notifications-list');

        // CSRF Token for AJAX Requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });

        Pusher.logToConsole = true;

        var pusher = new Pusher('bfb378da684dcc605648', {
            cluster: 'ap1',
            encrypted: true
        });

        var channel = pusher.subscribe('admin-notification');
        channel.bind('low-stock-event', function(data) {
            console.log("Received data from Pusher:", data);
            if (data && data.product) {
                const productName = data.product.name;
                const productQuantity = data.product.quantity;
                toastr.warning(`${productName} stock is low. Only ${productQuantity} left in stock.`);
            } else {
                console.error('Product data is missing:', data);
            }

            // Increment notification count locally
            updateNotificationCountAndList();
        });

        channel.bind('contact-message-event', function(data) {
            console.log("Received contact-message-event from Pusher:", data);
            const contactMessage = data.contactMessage;
            toastr.success(
                `New message from ${contactMessage.name} (${contactMessage.email}): ${contactMessage.message}`
            );

            // Increment notification count locally
            updateNotificationCountAndList();
        });

        function updateNotificationCountAndList() {
            console.log('Fetching updated notification count and list...');
            $.ajax({
                url: '/notifications/latest', // Backend endpoint to fetch notifications
                method: 'GET',
                success: function(response) {
                    console.log('Notifications fetched:', response);

                    // Update notification count
                    if (response.unreadCount !== undefined) {
                        if (response.unreadCount > 0) {
                            $('.notification-count').text(response.unreadCount).show();
                        } else {
                            $('.notification-count').text('').hide();
                        }
                    }

                    // Update notification list
                    const notificationList = $('#notifications-list');

                    // Preserve the "Select All" section
                    const selectAllSection = notificationList.find('.select-all').detach();

                    // Clear only the dynamic notification items
                    notificationList.empty();

                    // Re-add the preserved "Select All" section
                    notificationList.append(selectAllSection);

                    // Add the latest notifications
                    if (response.notifications && response.notifications.length > 0) {
                        response.notifications.forEach(notification => {
                            const isUnread = notification.read_at === null;
                            const iconClass = notification.icon ||
                            'fas fa-bell'; // Default icon if not provided
                            const notificationHtml = `
                        <li class="message-item notification-item ${isUnread ? 'unread' : 'read'}"
                            data-notification-id="${notification.id}">
                            <input type="checkbox" class="notification-checkbox" />
                            <div class="image">
                                <i class="${iconClass}"></i>
                            </div>
                            <div class="notification-content">
                                <a href="${notification.redirect_route || '#'}" class="notification-link">
                                    <div class="body-title-2 ${isUnread ? 'text-warning' : 'text-muted'}">
                                        ${notification.message}
                                    </div>
                                </a>
                                <div class="text-tiny ${isUnread ? '' : 'text-muted'}">
                                    ${notification.created_at}
                                </div>
                            </div>
                        </li>
                    `;
                            notificationList.append(notificationHtml);
                        });
                    } else {
                        notificationList.append(
                            '<li class="no-notifications"><div class="text-tiny">No unread notifications</div></li>'
                            );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching notifications:', xhr.responseText);
                },
            });
        }
    </script>

    <style>
        .notification-item.selected {
            background-color: #f1f1f1 !important;
            border-left: 3px solid #007bff !important;
        }

        #select-all {
            width: 20px !important;
            height: 20px !important;
            margin-right: 5px !important;
        }
    </style>
    @stack('styles')
</head>

<body class="body">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap">

                {{-- <div id="preload" class="preload-container">
                    <div class="preloading">
                        <span></span>
                    </div>
                </div> --}}

                <div class="section-menu-left">
                    <div class="box-logo">
                        <a href="{{ route('admin.index') }}" class="" style="width: 50px;">
                            <img src="{{ asset('../images/logo.png') }}" alt="site icon">
                        </a>
                        <div class="button-show-hide">
                            <i class="icon-menu-left"></i>
                        </div>
                    </div>
                    <div class="center">
                        <div class="center-item">
                            <div class="center-heading">Main Home</div>
                            <ul class="menu-list">
                                <li class="menu-item">
                                    <a href="{{ route('admin.index') }}" class="">
                                        <div class="icon"><i class="icon-grid"></i></div>
                                        <div class="text">Dashboard</div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="center-item">
                            <ul class="menu-list">

                                <li class="menu-item has-children">
                                    <a href="javascript:void(0);" class="menu-item-button">
                                        <div class="icon"><i class="icon-shopping-bag"></i></div>
                                        <div class="text">Facilities</div>
                                    </a>
                                    <ul class="sub-menu">
                                        <li class="sub-menu-item">
                                            <a href="{{ route('admin.facilities.create') }}">
                                                <div class="text">Add Facility</div>
                                            </a>
                                        </li>
                                        <li class="sub-menu-item">
                                            <a href="{{ route('admin.facilities.index') }}">
                                                <div class="text">View Facilities</div>
                                            </a>
                                        </li>
                                        <li class="divider mb-10"></li>
                                        <li class="sub-menu-item">
                                            <a href="{{ route('admin.product-attribute-add') }}">
                                                <div class="text">Add Facility Attributes</div>
                                            </a>
                                        </li>
                                        <li class="sub-menu-item">
                                            <a href="{{ route('admin.product-attributes') }}">
                                                <div class="text">View Facility Attributes</div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                {{-- Admin Menu Items --}}
                                @if (auth()->user()->utype === 'ADM')
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-layers"></i></div>
                                            <div class="text">Category</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.category.add') }}">
                                                    <div class="text">Add Category</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.categories') }}">
                                                    <div class="text">Categories</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-shopping-bag"></i></div>
                                            <div class="text">Products</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.product.add') }}">
                                                    <div class="text">Add Product</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.products') }}">
                                                    <div class="text">View Products</div>
                                                </a>
                                            </li>
                                            <li class="divider mb-10"></li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.product-attribute-add') }}">
                                                    <div class="text">Add Product Attributes</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.product-attributes') }}">
                                                    <div class="text">View Product Attributes</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="menu-item">
                                        <a href="{{ route('admin.orders') }}">
                                            <div class="icon"><i class="icon-file-plus"></i></div>
                                            <div class="text">Product Orders</div>
                                        </a>
                                    </li>

                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-home"></i></div>
                                            <div class="text">Rentals</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.rental.add') }}">
                                                    <div class="text">Add Rentals</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.rentals') }}">
                                                    <div class="text">View Rentals</div>
                                                </a>
                                            </li>

                                        </ul>
                                    </li>

                                    <li class="menu-item">
                                        <a href="{{ route('admin.reservation') }}">
                                            <div class="icon"><i class="icon-calendar"></i></div>
                                            <div class="text">Rental Reservations</div>
                                        </a>
                                    </li>

                                    <li class="menu-item">
                                        <a href="{{ route('admin.slides') }}">
                                            <div class="icon"><i class="icon-image"></i></div>
                                            <div class="text">Slides</div>
                                        </a>
                                    </li>

                                    <li class="menu-item">
                                        <a href="{{ route('admin.contacts') }}">
                                            <div class="icon"><i class="icon-mail"></i></div>
                                            <div class="text">Messages</div>
                                        </a>
                                    </li>

                                    <li class="menu-item">
                                        <a href="{{ route('admin.users') }}">
                                            <div class="icon"><i class="icon-user"></i></div>
                                            <div class="text">Users</div>
                                        </a>
                                    </li>
                                @endif

                                {{-- Director Menu Items --}}
                                @if (auth()->user()->utype === 'DIR' || auth()->user()->utype === 'ADM')
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="">
                                            <div class="icon"><i class="icon-bar-chart-2"></i></div>
                                            <div class="text">Products Reports</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.reports') }}">
                                                    <div class="text">Sales</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-product') }}">
                                                    <div class="text">Products</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-user') }}">
                                                    <div class="text">Users</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-inventory') }}">
                                                    <div class="text">Inventory</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-statements') }}">
                                                    <div class="text">Statements</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.user-reports') }}">
                                                    <div class="text">Input User</div>
                                                </a>
                                            </li>

                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.generate-input-sales') }}">
                                                    <div class="text">Input Sales</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="">
                                            <div class="icon"><i class="icon-bar-chart-2"></i></div>
                                            <div class="text">Rentals Reports</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.rentals_reports') }}">
                                                    <div class="text">Sales Reports</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.rentals-reports') }}">
                                                    <div class="text">Sales Input Reports</div>
                                                </a>
                                            </li>
                                            
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.rentalsReportsName') }}">
                                                    <div class="text">Reservation Reports</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>


                                    <!-- <li class="menu-item">
                                        <a href="#">
                                            <div class="icon"><i class="icon-settings"></i></div>
                                            <div class="text">Settings</div>
                                        </a>
                                    </li> -->
                                @endif

                            </ul>
                        </div>

                    </div>
                </div>
                <div class="section-content-right">

                    <div class="header-dashboard">
                        <div class="wrap">
                            <div class="header-left">
                                <a href="index-2.html">
                                    <img class="" alt="" src="{{ asset('../images/logo.png') }}"
                                        style="width: 50px;">
                                </a>
                                <div class="button-show-hide">
                                    <i class="icon-menu-left"></i>
                                </div>


                                <form class="form-search flex-grow" id="searchForm"
                                    action="{{ route('admin.searchproduct') }}" method="GET">
                                    <fieldset class="name">
                                        <input type="text" placeholder="Search here..." class="show-search"
                                            name="query" id="searchQuery" required />
                                    </fieldset>
                                    <div class="button-submit">
                                        <button type="submit"><i class="icon-search"></i></button>
                                    </div>
                                </form>


                            </div>
                            <div class="header-grid">
                                <div class="popup-wrap message type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-item">
                                                @if (auth()->user()->unreadNotifications->count())
                                                    <span
                                                        class="text-tiny notification-count">{{ auth()->user()->unreadNotifications->count() }}</span>
                                                @endif
                                                <i class="icon-bell"></i>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end has-content"
                                            aria-labelledby="dropdownMenuButton2">
                                            <li>
                                                <h6>Notifications</h6>
                                            </li>
                                            <div id="notifications-list">
                                                <li class="select-all">
                                                    <input type="checkbox" id="select-all" /> Select All
                                                    <button type="button" id="mark-all-as-read-btn"
                                                        class="btn btn-sm btn-light">Mark as Read</button>
                                                    <button type="button" id="delete-selected-btn"
                                                        class="btn btn-sm btn-danger">Delete Selected</button>
                                                </li>
                                                @forelse(auth()->user()->notifications as $notification)
                                                    @php
                                                        $iconClass = match ($notification->type) {
                                                            'App\\Notifications\\LowStockNotification'
                                                                => 'fa-solid fa-box',
                                                            'App\\Notifications\\ContactReceivedMessage'
                                                                => 'fas fa-envelope',
                                                            'App\\Notifications\\OrderNotification'
                                                                => 'fas fa-shopping-cart',
                                                            default => 'fas fa-bell',
                                                        };

                                                        $redirectRoute = match ($notification->type) {
                                                            'App\\Notifications\\LowStockNotification' => route(
                                                                'admin.products',
                                                            ),
                                                            'App\\Notifications\\ContactReceivedMessage' => route(
                                                                'admin.contacts',
                                                            ),
                                                            'App\\Notifications\\OrderNotification' => route(
                                                                'admin.orders',
                                                            ),
                                                            default => '#',
                                                        };
                                                    @endphp
                                                    <li class="message-item notification-item {{ $notification->read_at ? 'read' : 'unread' }}"
                                                        data-notification-id="{{ $notification->id }}">
                                                        <input type="checkbox" class="notification-checkbox" />
                                                        <div class="image">
                                                            <i class="{{ $iconClass }}"></i>
                                                        </div>
                                                        <div class="notification-content">
                                                            <!-- Dynamic redirection based on notification type -->
                                                            <a href="{{ $redirectRoute }}" class="notification-link">
                                                                <div
                                                                    class="body-title-2 {{ $notification->read_at ? 'text-muted' : 'text-warning' }}">
                                                                    {{ $notification->data['message'] ?? 'No message available' }}
                                                                </div>
                                                            </a>
                                                            <div
                                                                class="text-tiny {{ $notification->read_at ? 'text-muted' : '' }}">
                                                                {{ $notification->created_at->diffForHumans() }}
                                                            </div>
                                                        </div>
                                                    </li>
                                                @empty
                                                    <li class="no-notifications">
                                                        <div class="text-tiny">No unread notifications</div>
                                                    </li>
                                                @endforelse
                                            </div>
                                        </ul>
                                    </div>
                                </div>



                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        @if (Auth::check() && Auth::user()->utype == 'ADM' || Auth::check() && Auth::user()->utype == 'DIR')
                                        <div class="popup-wrap user type-header">
                                            <div class="dropdown">
                                                <!-- Dropdown Button -->
                                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                                        id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="header-user wg-user d-flex align-items-center">
                                                        <span class="image">
                                                            @if(Auth::user()->profile_image)
                                                                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image"
                                                                     class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                            @else
                                                                <img src="{{ asset('images/profile.jpg') }}" alt="Default Profile Image"
                                                                     class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                            @endif
                                                        </span>
                                                        <span class="flex flex-column ms-2">
                                                            <span class="body-title mb-2">{{ strtok(Auth::user()->name, ' ') }}</span>
                                                            <span class="text-tiny">
                                                                @if(Auth::user()->utype === 'ADM')
                                                                Admin
                                                                @elseif(Auth::user()->utype === 'DIR')
                                                                Director
                                                                @endif
                                                            </span>
                                                        </span>
                                                    </span>
                                                </button>
                                    
                                                <!-- Dropdown Menu -->
                                                <ul class="dropdown-menu dropdown-menu-end has-content"
                                                    aria-labelledby="dropdownMenuButton3">
                                                    <li>
                                                        <a href="{{route('admin.profile.index')}}" class="user-item">
                                                            <div class="icon">
                                                                <i class="icon-user"></i>
                                                            </div>
                                                            <div class="body-title-2">Account</div>
                                                        </a>
                                                    </li>
                                    
                                                    <li>
                                                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                                            @csrf
                                                            <a href="{{ route('logout') }}" class="user-item"
                                                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                                <div class="icon">
                                                                    <i class="icon-log-out"></i>
                                                                </div>
                                                                <div class="body-title-2">Log out</div>
                                                            </a>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="main-content">

                        @yield('content')


                        <div class="bottom-page">
                            <div class="body-text">Copyright © 2024</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/apexcharts/apexcharts.js') }}"></script> --}}
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchQuery');
            const searchResults = document.getElementById('searchResults');
            const menuItems = document.querySelectorAll('.menu-item, .sub-menu-item');

            // Search functionality
            searchInput.addEventListener('input', function() {
                const query = searchInput.value.toLowerCase().trim();
                searchResults.innerHTML = ''; // Clear previous results

                if (query) {
                    menuItems.forEach(function(item) {
                        const text = item.textContent.toLowerCase();
                        if (text.includes(query)) {
                            const resultItem = document.createElement('li');
                            const link = item.querySelector('a');
                            if (link) {
                                resultItem.innerHTML = `<a href="${link.href}">${text}</a>`;
                            } else {
                                resultItem.textContent = text;
                            }
                            searchResults.appendChild(resultItem);
                        }
                    });
                }
            });

        });


        document.addEventListener('DOMContentLoaded', function() {

            const notificationCount = $('.notification-count');
            const notificationList = $('#notifications-list');

            // CSRF token setup for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Handle "Select All" checkbox
            $('#select-all').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.notification-checkbox').prop('checked', isChecked);
            });

            // Handle "Mark Selected" button click
            $('#mark-all-as-read-btn').on('click', function() {
                const unreadIds = $('.notification-item.unread').map(function() {
                    return $(this).data('notification-id');
                }).get();

                console.log('Mark All as Read clicked. Unread IDs:', unreadIds);

                if (unreadIds.length > 0) {
                    markMultipleAsRead(unreadIds);
                } else {
                    alert('No unread notifications to mark as read.');
                }
            });

            // Handle "Delete Selected" button click
            $('#delete-selected-btn').on('click', function() {
                const selectedIds = getSelectedNotificationIds();
                console.log('Delete Selected clicked. Selected IDs:', selectedIds);

                if (selectedIds.length > 0) {
                    deleteMultipleNotifications(selectedIds);
                } else {
                    alert('No notifications selected.');
                }
            });

            // Get selected notification IDs
            function getSelectedNotificationIds() {
                const ids = $('.notification-checkbox:checked')
                    .map(function() {
                        return $(this).closest('.notification-item').data('notification-id');
                    })
                    .get();

                console.log('Selected Notification IDs:', ids);
                return ids;
            }
            // Mark multiple notifications as read
            function markMultipleAsRead(notificationIds) {
                console.log('Sending mark as read request. IDs:', notificationIds);

                $.ajax({
                    url: '/notifications/mark-read-multiple',
                    method: 'POST',
                    data: {
                        notification_ids: notificationIds,
                    },
                    success: function(response) {
                        console.log('Mark as Read success response:', response);

                        if (response.status === 'success') {
                            notificationIds.forEach((id) => {
                                const notification = $(
                                    `.notification-item[data-notification-id="${id}"]`);
                                console.log('Updating notification DOM for ID:', id);

                                notification.removeClass('unread').addClass('read');
                                notification.find('.body-title-2').removeClass('text-warning')
                                    .addClass('text-muted');
                                notification.find('.text-tiny').addClass('text-muted');
                            });

                            updateNotificationCount(response.unreadCount);
                        } else {
                            console.error('Error in response:', response);
                            alert('Failed to mark notifications as read.');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error marking notifications as read:', xhr.responseText);
                        alert('Failed to mark notifications as read. Please try again.');
                    },
                });
            }

            // Delete selected notifications
            function deleteMultipleNotifications(notificationIds) {
                $.ajax({
                    url: '/notifications/delete-multiple',
                    method: 'POST',
                    data: {
                        notification_ids: notificationIds
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            notificationIds.forEach(function(id) {
                                $(`.notification-item[data-notification-id="${id}"]`).fadeOut(
                                    300,
                                    function() {
                                        $(this).remove();
                                    });
                            });

                            updateNotificationCount();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error deleting notifications:', xhr.responseText);
                        alert('Failed to delete notifications. Please try again.');
                    }
                });
            }

            // Update notification count dynamically
            function updateNotificationCount(unreadCount) {
                console.log('Updating notification count. Unread Count:', unreadCount);

                if (unreadCount > 0) {
                    $('.notification-count').text(unreadCount).show();
                } else {
                    $('.notification-count').text('').hide();
                }
            }


        });
    </script>



    @stack('scripts')
</body>

</html>
