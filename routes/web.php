<?php

use App\Models\Contact;
use App\Models\Product;
use App\Events\LowStockEvent;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AuthDirector;
use Illuminate\Support\Facades\Route;
use App\Events\ContactMessageReceived;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AdminProfileController;

Auth::routes(['reset' => true]);

Route::get('/test', function () {
    $product = Product::with('attributeValues')->find(4); // Adjust the product ID as necessary

    if (!$product) {
        Log::error('Product with ID 4 not found!');
        return response()->json(['error' => 'Product not found'], 404);
    }

    // Log the attribute values for debugging
    Log::info('Attribute Values for product: ', $product->attributeValues->toArray());

    // Determine quantity
    $quantity = $product->attributeValues->isNotEmpty() 
        ? $product->attributeValues->sum('quantity') // Aggregate quantities
        : $product->quantity; // Use main quantity if no variants

    Log::info('Low stock alert for product: ' . $product->name . ', Quantity: ' . $quantity);

    // Dispatch the event with product and determined quantity
    event(new LowStockEvent($product, $quantity));

    return response()->json(['success' => 'Event fired']); // This will just return a simple success message
});


Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index'); 
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');




Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
Route::get('/rentals/{rental_slug}', [RentalController::class, 'show'])->name('rentals.details');
Route::post('/rentals/{rental_id}/checkout', [RentalController::class, 'checkout_details'])->name('rentals.checkout.details');

Route::get('/rentals/{rental_id}/checkout', [RentalController::class, 'checkout'])->name('rentals.checkout');
Route::get('/rentals/{rental_id}/process/checkout', [RentalController::class, 'processCheckout'])->name('rentals.process.checkout');


Route::post('/reserve/{rentalId}', [RentalController::class, 'placeReservation'])->name('rentals.reserve.events');

// Route::get('/checkout/{rental_id}', [RentalController::class, 'checkout'])->name('rentals.checkout');
Route::get('/api/check-pool-capacity/{rentalId}/{date}', [RentalController::class, 'checkPoolCapacity'])->name('check.pool.capacity');
Route::get('/api/rentals/{rentalId}/reservations', [RentalController::class, 'getReservations']);
Route::get('/rental/checkout/{rentalId}/reservations', [RentalController::class, 'getReservations'])->name('rental.checkout');
Route::get('/api/holidays', [RentalController::class, 'getHolidays']);

Route::get('/about-us', [AboutController::class, 'index'])->name('about.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::put('/cart/qty/increase/{rowId}', [CartController::class, 'increase_cart_quantity']);
Route::put('/cart/qty/decrease/{rowId}', [CartController::class, 'decrease_cart_quantity']);
Route::put('/cart/update-variant/{rowId}', [CartController::class, 'updateVariant'])->name('cart.item.updateVariant');

Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
Route::delete('/cart/clear', [CartController::class, 'empty_cart'])->name('cart.empty');

Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/place-an-order', [CartController::class, 'place_an_order'])->name('cart.place.an.order');



Route::get('/order-confirmation', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');

Route::get('/preorders/accept/{preOrder}', [CartController::class, 'acceptPreOrder'])->name('preorders.accept');
Route::get('/preorders/cancel/{preOrder}', [CartController::class, 'cancelPreOrder'])->name('preorders.cancel');

Route::get('/api/get-time-slots', [CartController::class, 'getAvailableDatesAndTimeSlots']);
Route::get('/api/get-unavailable-dates', [CartController::class, 'getUnavailableDates']);
Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('google-auth-callback');
Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('google-auth');

Route::get('/contact-us', [HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact-us', [HomeController::class, 'contact_store'])->name('home.contact.store');

Route::get('/search', [HomeController::class, 'search'])->name('home.search');



Route::middleware(['auth'])->group(function () {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-order', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/account-order/{order_id}/details', [UserController::class, 'order_details'])->name('user.order.details');
    Route::put('/account-order/cancel-order', [UserController::class, 'order_cancel'])->name('user.order.cancel');

    Route::get('/user/profile', [UserController::class, 'show_profile'])->name('user.profile');
    Route::get('/user/profile/edit/{id}', [UserController::class, 'profile_edit'])->name('user.profile.edit');
    Route::put('/user/profile/update', [UserController::class, 'profile_update'])->name('user.profile.update');
    // Route::get('user/profile/edit/{id}', [UserController::class, 'edit'])->name('user.profile.edit');


    Route::get('/user/profile-image/edit', [UserController::class, 'profile_image_edit'])->name('user.profile.image.edit');
    Route::put('/user/profile-image/update', [UserController::class, 'profile_image_update'])->name('user.profile.image.update');
    Route::delete('/user/profile-image/delete', [UserController::class, 'profile_image_delete'])->name('user.profile.image.delete');

    Route::get('/order-history', [UserController::class, 'order_history'])->name('user.order.history');
    Route::get('/reservation-history', [UserController::class, 'reservation_history'])->name('user.reservation.history');
    

    Route::get('/user/reservation', [UserController::class, 'account_reservation'])->name('user.reservation');
    Route::get('/user/reservation-details/{reservation_id}', [UserController::class, 'account_reservation_details'])->name('user.reservation-details');



    Route::post('/user/reservation-details/cancel-reservation', [UserController::class, 'account_cancel_reservation'])->name('user.account_cancel_reservation');
    Route::get('/api/check-pool-capacity/{rentalId}/{quantity}', [RentalController::class, 'checkPoolCapacity']);






    Route::get('/account-rentals', [UserController::class, 'account_rentals'])->name('user.account.rentals');
});



Route::get('password/set', [PasswordController::class, 'showSetPasswordForm'])->name('password.set');
Route::post('password/set', [PasswordController::class, 'setPassword']);


Route::middleware(['auth', AuthAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');
    Route::post('/admin/category/store', [AdminController::class, 'category_store'])->name('admin.category.store');
    Route::get('/admin/category/edit/{id}', [AdminController::class, 'category_edit'])->name('admin.category.edit');
    Route::put('/admin/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');
    Route::delete('/admin/category/{id}/delete', [AdminController::class, 'category_delete'])->name('admin.category.delete');


    // Profile for the admin 
    Route::get('/admin/profile', [AdminProfileController::class, 'show_profile'])->name('admin.profile.index');    


    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/admin/product/add', [AdminController::class, 'product_add'])->name('admin.product.add');
    Route::post('/admin/product/store', [AdminController::class, 'product_store'])->name('admin.product.store');
    Route::get('/admin/product/edit/{id}', [AdminController::class, 'product_edit'])->name('admin.product.edit');
    Route::put('/admin/product/update', [AdminController::class, 'product_update'])->name('admin.product.update');
    // Route::delete('/admin/product/{id}/delete', [AdminController::class, 'product_delete'])->name('admin.product.delete');
    Route::delete('/admin/product/{id}/archived', [AdminController::class, 'archivedProducts'])->name('admin.product.archive');
    Route::get('/admin/archived-products', [AdminController::class, 'showArchivedProducts'])->name('admin.archived-products');
    Route::post('/admin/product/restore', [AdminController::class, 'restoreProducts'])->name('admin.product.restore');
    Route::post('/admin/product/delete', [AdminController::class, 'deleteProducts'])->name('admin.product.delete');
    Route::get('/admin/products/search', [AdminController::class, 'searchProducts'])->name('admin.products.search');

    Route::get('/admin/product-attributes', [AdminController::class, 'prod_attributes'])->name('admin.product-attributes');
    Route::get('/admin/product-attribute/add', [AdminController::class, 'prod_attribute_add'])->name('admin.product-attribute-add');
    Route::post('/admin/product-attributes/store', [AdminController::class, 'prod_attribute_store'])->name('admin.product.attribute.store');
    Route::get('/admin/product-attribute/edit/{id}', [AdminController::class, 'product_attribute_edit'])->name('admin.product.attribute.edit');
    Route::put('/admin/product-attribute/update', [AdminController::class, 'product_attribute_update'])->name('admin.product.attribute.update');
    Route::delete('/admin/product-attribute/{id}/delete', [AdminController::class, 'product_attribute_delete'])->name('admin.product.attribute.delete');


    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/orders/filter', [AdminController::class, 'order_filter'])->name('orders.filter');
    Route::get('/admin/filter-reservations', [AdminController::class, 'filterReservations']);


    Route::get('/admin/order/{order_id}/details', [AdminController::class, 'order_details'])->name('admin.order.details');
    // Route::get('/admin/order/{order_id}/details', [AdminController::class, 'showOrderDetails'])->name('admin.order.details');
    Route::put('/admin/order/update-status', [AdminController::class, 'update_order_status'])->name('admin.order.status.update');
    Route::get('/admin/orders/filter', [AdminController::class, 'filterOrders'])->name('admin.orders.filter');
    

    Route::get('/admin/slide', [AdminController::class, 'slides'])->name('admin.slides');
    Route::get('/admin/slide/add', [AdminController::class, 'slide_add'])->name('admin.slide.add');
    Route::post('/admin/slide/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
    Route::get('/admin/slide/{id}/edit', [AdminController::class, 'slide_edit'])->name('admin.slide.edit');
    Route::put('/admin/slide/update', [AdminController::class, 'slide_update'])->name('admin.slide.update');
    Route::delete('/admin/slide/{id}/delete', [AdminController::class, 'slide_delete'])->name('admin.slide.delete');

    Route::get('/admin/contact', [AdminController::class, 'contacts'])->name('admin.contacts');
    Route::delete('/admin/contact/{id}/delete', [AdminController::class, 'contact_delete'])->name('admin.contact.delete');
    Route::post('/admin/contact/{id}/reply', [AdminController::class, 'contact_reply'])->name('admin.contact.reply');
    // Route::post('/notifications/{id}/mark-read', [AdminController::class, 'markAsRead']);
    // Route::post('/notifications/mark-read-multiple', [AdminController::class, 'markMultipleAsRead']);
    Route::get('/notifications/count', [AdminController::class, 'getUnreadNotificationCount']);
    Route::post('/notifications/mark-read-multiple', [AdminController::class, 'markMultipleAsRead']);
    Route::post('/notifications/mark-read/{id}', [AdminController::class, 'markAsRead']);
    Route::get('/notifications/unread-count', [AdminController::class, 'unreadCount']);
    Route::post('/notifications/delete-multiple', [AdminController::class, 'deleteMultipleNotifications']);
    Route::get('/notifications/latest', [AdminController::class, 'latest']);


    


    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/user/filter', [AdminController::class, 'filter'])->name('admin.users.filter');
    Route::get('/user/search', [AdminController::class, 'search'])->name('admin.users.search');
    Route::delete('/users/{id}', [AdminController::class, 'users_destroy'])->name('admin.users.destroy');
    Route::get('admin/users/{id}/edit', [AdminController::class, 'users_edit'])->name('admin.users.edit');
    Route::put('admin/users/{id}/update', [AdminController::class, 'users_update'])->name('admin.users.update');
    Route::get('/admin/add', [AdminController::class, 'users_add'])->name('admin.users.add');
    Route::post('/admin/store', [AdminController::class, 'users_store'])->name('admin.users.store');
    
    Route::get('/admin/search', [AdminController::class, 'searchproduct'])->name('admin.searchproduct');


    Route::get('/admin/index-weekly', [AdminController::class, 'indexWeekly'])->name('admin.index-weekly');
    Route::get('/admin/getWeeklyData', [AdminController::class, 'getWeeklyData'])->name('admin.getWeeklyData');
    Route::get('/admin/index-daily', [AdminController::class, 'indexDaily'])->name('admin.index-daily');

    Route::get('/admin/reports', [AdminController::class, 'generateReport'])->name('admin.reports');
    Route::get('/admin/report-user', [AdminController::class, 'generateUser'])->name('admin.report-user');
    Route::get('/admin/report-product', [AdminController::class, 'generateProduct'])->name('admin.report-product');
    Route::get('/admin/report-inventory', [AdminController::class, 'generateInventory'])->name('admin.report-inventory');
    Route::get('/admin/report-statements', [AdminController::class, 'listBillingStatements'])->name('admin.report-statements');
    Route::get('/admin/report-statement/{orderId}', [AdminController::class, 'generateBillingStatement'])->name('admin.report-statement');
    

    
    Route::get('/user-reports', [AdminController::class, 'showUserReports'])->name('admin.user-reports');
    Route::post('/user-reports/generate', [AdminController::class, 'generateUserReports'])->name('admin.user-reports.generate');
    Route::post('/admin/sales-report', [AdminController::class, 'generateInputSales'])->name('admin.generate-input-sales');
    Route::get('/admin/sales-report', function () {return view('admin.input-sales'); });
    
    Route::post('/admin/sales-report/download', [AdminController::class, 'downloadInputSales'])->name('admin.download-input-sales');
    Route::post('/user-reports/download', [AdminController::class, 'downloadInputUsers'])->name('admin.user-reports.download');

    Route::post('/rentals-reports/generate', [AdminController::class, 'generateInputRentalReports'])->name('admin.generate-input-rentals-reports');
    Route::get('/rentals-reports', function () {return view('admin.input-rentals-reports');})->name('admin.rentals-reports');
    Route::post('/rentals-reports/download', [AdminController::class, 'downloadInputRentalsReports'])->name('admin.download-input-rentals-reports');











    Route::get('/admin/report-statement/{orderId}', [AdminController::class, 'generateBillingStatement'])->name('admin.report-statement');
    Route::get('/admin/report-statements/download', [AdminController::class, 'downloadBillingStatements'])->name('admin.report-statements.download');
    Route::post('/admin/downloadPdf', [AdminController::class, 'downloadPdf'])->name('admin.downloadPdf');
    Route::get('/admin/report-user/pdf', [AdminController::class, 'downloadUserReportPdf'])->name('admin.report-user.pdf');
    Route::get('/admin/report-inventory/pdf', [AdminController::class, 'downloadInventoryReportPdf'])->name('admin.report-inventory.pdf');

    Route::get('/admin/rentals', [AdminController::class, 'rentals'])->name('admin.rentals');
    Route::get('/admin/rentals/add', [AdminController::class, 'rental_add'])->name('admin.rental.add');
    Route::post('/admin/rentals/store', [AdminController::class, 'rental_store'])->name('admin.rental.store');
    Route::get('/admin/rental/edit/{id}', [AdminController::class, 'rental_edit'])->name('admin.rental.edit');
    Route::put('/admin/rental/update', [AdminController::class, 'rental_update'])->name('admin.rental.update');
    Route::delete('/admin/rental/delete/{id}', [AdminController::class, 'rental_delete'])->name('admin.rental.delete');

    Route::get('/admin/rentals_reports', [AdminController::class, 'rentalsReports'])->name('admin.rentals_reports');
    Route::post('/admin/rentals-reports/download-pdf', [AdminController::class, 'downloadPdfRentals'])->name('admin.downloadPdfRentals');
    Route::get('admin/rentals-reports-name', [AdminController::class, 'rentalsReportsName'])->name('admin.rentalsReportsName');
    Route::post('admin/download-pdf-rentals-name', [AdminController::class, 'downloadPdfRentalsName'])->name('admin.downloadPdfRentalsName');
    Route::get('/admin/report-product/download', [AdminController::class, 'downloadProduct'])->name('admin.report-product.download');
    

    


    Route::get('/admin/reservation/{reservation_id}/events', [AdminController::class, 'event_items'])->name('admin.reservation-events');
    Route::get('/admin/reservation-history/{reservation_id}', [AdminController::class, 'reservationHistory'])->name('admin.reservation-history');
    Route::post('/admin/update-reservation-status/', [AdminController::class, 'updateStatus'])->name('admin.updateReservationStatus');
    Route::post('/admin/update-payment-status', [AdminController::class, 'updatePaymentStatus'])->name('admin.updatePaymentStatus');
    // Route::post('/admin/reservation/{reservation}/update-status', [AdminController::class, 'updateStatus'])->name('admin.update-status');
    // Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.reservatioiindex');

    Route::post('/admin/reservation/{reservation_id}/update-status', [AdminController::class, 'updateReservationStatus'])->name('admin.update-reservation-status');


    Route::get('/admin/reservation', [AdminController::class, 'reservations'])->name('admin.reservation');
 
    
    
    

    Route::prefix('admin')->group(function () {
        Route::prefix('facilities')->group(function () {
            Route::get('/', [FacilityController::class, 'index'])->name('admin.facilities.index');
            Route::get('/create', [FacilityController::class, 'create'])->name('admin.facilities.create');
            Route::post('/store', [FacilityController::class, 'store'])->name('admin.facilities.store');

            Route::put('/update', [AdminController::class, 'update'])->name('admin.facilities.update');


        });
    });
    
});

