<?php

namespace App\Http\Controllers;


use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\User;
use App\Models\Order;
use App\Models\Slide;
use App\Models\Rental;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Category;
use App\Models\PreOrder;
use App\Models\WeekName;
use App\Models\MonthName;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Mail\ReplyToContact;
use Illuminate\Http\Request;
use App\Models\DormitoryRoom;
use App\Models\ContactReplies;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductAttribute;
use App\Mail\LowStockNotification;
use Illuminate\Support\Facades\Validator;
use App\Notifications\StockUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\Laravel\Facades\Image;
use App\Notifications\PreOrderBackInStockNotification;


class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Get the current year and year from the request, or default to the current year
        $currentYear = Carbon::now()->year;
        $selectedYear = $request->input('year', $currentYear);

        // Fetch the latest 10 orders
        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

        // Filter products to include only those with stock status as 'Reorder' or 'Out of Stock'
        $products = Product::all()->filter(function ($product) {
            $currentStock = $product->attributeValues->isNotEmpty()
                ? $product->attributeValues->sum('quantity')
                : $product->current_stock;
            return $currentStock <= $product->reorder_quantity; // Show only if Reorder or Out of Stock
        });

        // Dashboard data (overall totals) for the selected year
        $dashboardDatas = DB::select("SELECT 
                                        SUM(total) AS TotalAmount,
                                        SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                                        SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                                        SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                                        COUNT(*) AS Total,
                                        SUM(IF(status = 'reserved', 1, 0)) AS TotalReserved,
                                        SUM(IF(status = 'pickedup', 1, 0)) AS TotalPickedUp, 
                                        SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
                                    FROM Orders
                                    WHERE YEAR(created_at) = ?", [$selectedYear]);

        // Monthly Data for the selected year
        $monthlyDatas = DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
                                        IFNULL(D.TotalAmount, 0) AS TotalAmount,
                                        IFNULL(D.TotalReservedAmount, 0) AS TotalReservedAmount,
                                        IFNULL(D.TotalPickedUpAmount, 0) AS TotalPickedUpAmount,
                                        IFNULL(D.TotalCanceledAmount, 0) AS TotalCanceledAmount 
                                    FROM month_names M
                                    LEFT JOIN (
                                        SELECT 
                                            MONTH(created_at) AS MonthNo,
                                            SUM(total) AS TotalAmount,
                                            SUM(IF(status='reserved', total, 0)) AS TotalReservedAmount,
                                            SUM(IF(status='pickedup', total, 0)) AS TotalPickedUpAmount,
                                            SUM(IF(status='canceled', total, 0)) AS TotalCanceledAmount
                                        FROM Orders 
                                        WHERE YEAR(created_at) = ? 
                                        GROUP BY MONTH(created_at)
                                    ) D ON D.MonthNo = M.id
                                    ORDER BY M.id", [$selectedYear]);

        // Prepare Monthly Data for View
        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $ReservationAmountM = implode(',', collect($monthlyDatas)->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountM = implode(',', collect($monthlyDatas)->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());
        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalReservedAmount = collect($monthlyDatas)->sum('TotalReservedAmount');
        $TotalPickedUpAmount = collect($monthlyDatas)->sum('TotalPickedUpAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        // Calculate the range of years to show in the dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        $pageTitle = 'Admin Dashboard';

        // Return View with Monthly Data
        return view('admin.index', compact(
            'orders',
            'dashboardDatas',
            'AmountM',
            'ReservationAmountM',
            'PickedUpAmountM',
            'CanceledAmountM',
            'TotalAmount',
            'TotalReservedAmount',
            'TotalPickedUpAmount',
            'TotalCanceledAmount',
            'yearRange',
            'selectedYear',
            'pageTitle',
            'products'
        ));
    }

    public function indexWeekly(Request $request)
    {
        $availableMonths = MonthName::orderBy('id')->get();

        $currentDate = Carbon::now();
        $currentMonthId = $currentDate->month;
        $currentYear = $currentDate->year;
        $selectedMonthId = $request->input('month', $currentMonthId);
        $selectedYear = $request->input('year', $currentYear);

        $selectedMonth = $availableMonths->firstWhere('id', $selectedMonthId);

        // Dashboard data (overall totals) for the selected year
        $dashboardDatas = DB::select("SELECT 
                                        SUM(total) AS TotalAmount,
                                        SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                                        SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                                        SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                                        COUNT(*) AS Total,
                                        SUM(IF(status = 'reserved', 1, 0)) AS TotalReserved,
                                        SUM(IF(status = 'pickedup', 1, 0)) AS TotalPickedUp, 
                                        SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
                                    FROM Orders
                                    WHERE YEAR(created_at) = ?", [$selectedYear]);

        if (!$selectedMonth) {
            $selectedMonth = $availableMonths->first();
            $selectedMonthId = $selectedMonth->id;
        }

        // Define the start and end of the selected month
        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Calculate week ranges for the month
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            // Ensure the start and end of the week don't exceed the month boundaries
            if ($startOfWeek->lt($startOfMonth)) {
                $startOfWeek = $startOfMonth;
            }
            if ($endOfWeek->gt($endOfMonth)) {
                $endOfWeek = $endOfMonth;
            }

            // Add valid week ranges
            if ($startOfWeek->lte($endOfMonth)) {
                $weekRanges[$week] = [$startOfWeek, $endOfWeek];
            }
        }

        // Fetch products with stock information and filter by Reorder or Out of Stock status
        $products = Product::with(['category', 'attributeValues'])->get()->filter(function ($product) {
            $currentStock = $product->attributeValues->isNotEmpty()
                ? $product->attributeValues->sum('quantity')
                : $product->current_stock;
            return $currentStock <= $product->reorder_quantity; // Show only if Reorder or Out of Stock
        });

        // Fetch orders for the selected month and year
        $orders = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->orderBy('created_at', 'DESC')
            ->take(10) // Fetch the most recent 10 orders for display
            ->get();

        // Fetch totals for each week
        $totalAmounts = [];
        $reservationAmounts = [];
        $pickedUpAmounts = [];
        $canceledAmounts = [];

        foreach ($weekRanges as $week => [$startOfSelectedWeek, $endOfSelectedWeek]) {
            // Fetch total amounts for the week
            $dashboardData = DB::select(
                "SELECT 
                    SUM(total) AS TotalAmount,
                    SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                    SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                    SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                FROM Orders
                WHERE created_at BETWEEN ? AND ?",
                [$startOfSelectedWeek, $endOfSelectedWeek]
            )[0];

            $totalAmounts[$week] = $dashboardData->TotalAmount ?? 0;
            $reservationAmounts[$week] = $dashboardData->TotalReservedAmount ?? 0;
            $pickedUpAmounts[$week] = $dashboardData->TotalPickedUpAmount ?? 0;
            $canceledAmounts[$week] = $dashboardData->TotalCanceledAmount ?? 0;
        }

        // Prepare data for view
        $AmountW = implode(',', $totalAmounts);
        $ReservationAmountW = implode(',', $reservationAmounts);
        $PickedUpAmountW = implode(',', $pickedUpAmounts);
        $CanceledAmountW = implode(',', $canceledAmounts);
        $TotalAmountW = array_sum($totalAmounts);
        $TotalReservedAmountW = array_sum($reservationAmounts);
        $TotalPickedUpAmountW = array_sum($pickedUpAmounts);
        $TotalCanceledAmountW = array_sum($canceledAmounts);
        $pageTitle = 'Weekly Reports Dashboard';

        // Calculate the range of years to show in the dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        return view('admin.index-weekly', compact(
            'orders',
            'availableMonths',
            'selectedMonth',
            'selectedYear',
            'AmountW',
            'ReservationAmountW',
            'PickedUpAmountW',
            'CanceledAmountW',
            'TotalAmountW',
            'TotalReservedAmountW',
            'TotalPickedUpAmountW',
            'TotalCanceledAmountW',
            'yearRange',
            'pageTitle',
            'dashboardDatas',
            'products' // Pass the filtered products to the view
        ));
    }

    public function indexDaily(Request $request)
    {
        // Retrieve available months and weeks
        $availableMonths = MonthName::orderBy('id')->get();
        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();

        // Set default values based on the current date
        $currentDate = Carbon::now();
        $selectedMonthId = $request->input('month', $currentDate->month);
        $selectedYear = $request->input('year', $currentDate->year);
        $selectedWeekId = $request->input('week', $currentDate->weekOfMonth);

        // Get the selected month or default to the current one
        $selectedMonth = $availableMonths->firstWhere('id', $selectedMonthId) ?? $availableMonths->first();

        // Define the start and end of the selected month
        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Calculate week ranges within the selected month
        $weekRanges = [];
        $weekStart = $startOfMonth->copy()->startOfWeek();
        while ($weekStart->lte($endOfMonth)) {
            $weekEnd = $weekStart->copy()->endOfWeek()->min($endOfMonth);
            $weekRanges[] = [$weekStart->copy(), $weekEnd->copy()];
            $weekStart->addWeek();
        }

        // Validate selected week and set start/end of selected week
        if (!isset($weekRanges[$selectedWeekId - 1])) {
            $selectedWeekId = 1;
        }
        [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[$selectedWeekId - 1];

        // Fetch products with stock information and filter only "Reorder" or "Out of Stock"
        $products = Product::with(['category', 'attributeValues'])->get()->filter(function ($product) {
            $currentStock = $product->attributeValues->isNotEmpty()
                ? $product->attributeValues->sum('quantity')
                : $product->current_stock;
            return $currentStock <= $product->reorder_quantity; // Show only if Reorder or Out of Stock
        });

        // Fetch orders within the selected week (for table display)
        $orders = Order::whereBetween('created_at', [$startOfSelectedWeek, $endOfSelectedWeek])
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get();

        // Aggregate data for dashboard display
        $dashboardDatas = DB::select(
            "SELECT 
                SUM(total) AS TotalAmount,
                SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                COUNT(*) AS Total,
                SUM(IF(status = 'reserved', 1, 0)) AS TotalReserved,
                SUM(IF(status = 'pickedup', 1, 0)) AS TotalPickedUp,
                SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
              FROM Orders
              WHERE created_at BETWEEN ? AND ?",
            [$startOfSelectedWeek, $endOfSelectedWeek]
        );

        // Aggregate daily data for chart display
        $dailyDatas = DB::select(
            "SELECT DAYOFWEEK(created_at) AS DayNo,
                    DAYNAME(created_at) AS DayName,
                    SUM(total) AS TotalAmount,
                    SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                    SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                    SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
               FROM Orders 
               WHERE created_at BETWEEN ? AND ?
               GROUP BY DAYOFWEEK(created_at), DAYNAME(created_at)
               ORDER BY DayNo",
            [$startOfSelectedWeek, $endOfSelectedWeek]
        );

        // Ensure that dashboard data is structured as an array for view compatibility
        $dashboardDatas = $dashboardDatas ? [$dashboardDatas[0]] : [];

        // Prepare variables for chart rendering in the Blade view
        $AmountD = implode(',', collect($dailyDatas)->pluck('TotalAmount')->toArray());
        $ReservationAmountD = implode(',', collect($dailyDatas)->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountD = implode(',', collect($dailyDatas)->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountD = implode(',', collect($dailyDatas)->pluck('TotalCanceledAmount')->toArray());

        // Calculate total amounts for display
        $TotalAmountD = collect($dailyDatas)->sum('TotalAmount');
        $TotalReservedAmountD = collect($dailyDatas)->sum('TotalReservedAmount');
        $TotalPickedUpAmountD = collect($dailyDatas)->sum('TotalPickedUpAmount');
        $TotalCanceledAmountD = collect($dailyDatas)->sum('TotalCanceledAmount');

        // Define page title and year range for the dropdown
        $pageTitle = 'Reports Dashboard';
        $yearRange = range($currentDate->year, $currentDate->year - 10);

        // Return all required data to the view
        return view('admin.index-daily', compact(
            'orders',
            'dashboardDatas',
            'dailyDatas',
            'AmountD',
            'ReservationAmountD',
            'PickedUpAmountD',
            'CanceledAmountD',
            'TotalAmountD',
            'TotalReservedAmountD',
            'TotalPickedUpAmountD',
            'TotalCanceledAmountD',
            'selectedMonth',
            'selectedYear',
            'selectedWeekId',
            'availableMonths',
            'availableWeeks',
            'yearRange',
            'pageTitle',
            'products' // Pass the filtered products to the view
        ));
    }



    public function categories()
    {
        // $categories = Category::orderBy('id', 'DESC')->paginate(10);
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('id', 'DESC')
            ->paginate(10);
        $pageTitle = 'Category Dashboard';
        return view('admin.categories', compact('categories', 'pageTitle'));
    }

    public function category_add()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        $pageTitle = 'Category Add Dashboard';
        return view('admin.category-add', compact('pageTitle', 'parentCategories'));
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'unique:categories,slug',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'parent_id' => 'nullable|exists:categories,id'
        ], [
            'name.required' => 'The category name is required.',
            'slug.unique' => 'The slug must be unique. This slug is already taken.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = $request->parent_id;

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateCategoryThumbnailsImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been added successfully!');
    }






    public function category_edit($id)
    {
        $category =  Category::find($id);
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $id)
            ->get();
        return view('admin.category-edit', compact('category', 'parentCategories'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = $request->parent_id;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->GenerateCategoryThumbnailsImage($image, $file_name);
            $category->image = $file_name;
        }

        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been updated successfully!');
    }

    public function GenerateCategoryThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->getRealPath());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
            File::delete(public_path('uploads/categories') . '/' . $category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has been deleted successfully!');
    }


    public function products(Request $request)
    {
        $archived = $request->query('archived', 0);
        $products = Product::with(['category' => function ($query) {
            $query->with('parent');
        }, 'attributes'])
            ->where('archived', $archived)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('admin.products', compact('products', 'archived'));
    }

    public function product_add()
    {

        $categories = Category::with('children')->whereNull('parent_id')->get();
        $productAttributes = ProductAttribute::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'productAttributes'));
    }

    public function product_store(Request $request)
    {
        // Determine if the product has variants based on the presence of variant fields
        $hasVariant = $request->filled('variant_name') &&
            $request->filled('product_attribute_id') &&
            $request->filled('variant_price') &&
            $request->filled('variant_quantity');

        // Validate the incoming request data
        $request->validate([
            'name' => 'required',
            'slug' => 'unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'price' => $hasVariant ? 'nullable' : 'required|numeric',
            'quantity' => $hasVariant ? 'nullable' : 'required|integer',
            'featured' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'sex' => 'required|in:male,female,all',
            'category_id' => 'required|integer|exists:categories,id',

            // **Added Validation Rules for Stock Status Fields**
            // 'instock_quantity' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:0',
            'outofstock_quantity' => 'required|integer|min:0',
        ], [
            'category_id.integer' => 'Please select a valid category.',
            'sex.in' => 'Please select a valid gender category.',
            // **Optional Custom Messages for Stock Status Fields**
            // 'instock_quantity.required' => 'In Stock Quantity is required.',
            'reorder_quantity.required' => 'Reorder Quantity is required.',
            'outofstock_quantity.required' => 'Out of Stock Quantity is required.',
        ]);

        // Create a new Product instance and assign values from the request
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->price = $hasVariant ? null : $request->price;
        $product->quantity = $hasVariant ? null : $request->quantity;
        $product->stock_status = $hasVariant ? 'instock' : 'outofstock';
        $product->featured = $request->featured;
        $product->sex = $request->sex;
        $product->category_id = $request->category_id;
        $product->reorder_quantity = $request->reorder_quantity;
        $product->outofstock_quantity = $request->outofstock_quantity;
        $current_timestamp = Carbon::now()->timestamp;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailsImage($image, $imageName);
            $product->image = $imageName;
        }

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
                    $this->GenerateProductThumbnailsImage($file, $gFileName);
                    array_push($gallery_arr, $gFileName);
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }

        $product->images = $gallery_images;

        $product->save();

        // Handle variants
        if ($hasVariant && is_array($request->variant_name)) {
            $attributeValues = [];
            foreach ($request->variant_name as $index => $variantName) {
                $attributeValues[] = [
                    'product_id' => $product->id,
                    'product_attribute_id' => $request->product_attribute_id[$index],
                    'value' => $variantName,
                    'price' => $request->variant_price[$index],
                    'quantity' => $request->variant_quantity[$index],
                ];
            }

            foreach ($attributeValues as $value) {
                ProductAttributeValue::create($value);
            }


            $totalVariantQuantity = collect($attributeValues)->sum('quantity');

            if ($totalVariantQuantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($totalVariantQuantity <= $product->reorder_quantity && $totalVariantQuantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }

            $product->save();
        } else {
            // Determine stock status for products without variants
            if ($product->quantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($product->quantity <= $product->reorder_quantity && $product->quantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }
            $product->save();
        }

        return redirect()->route('admin.products')->with('status', 'Product has been added successfully!');
    }


    public function product_edit($id)
    {
        $product = Product::with('attributeValues')->findOrFail($id);
        // $categories = Category::select('id', 'name')->orderBy('name')->get();
        $categories = Category::with('children')->whereNull('parent_id')->get();
        $productAttributes = ProductAttribute::select('id', 'name')->orderBy('name')->get();

        $hasVariant = $product->attributeValues->isNotEmpty();
        $variantDetails = null;
        if ($hasVariant) {
            $variantDetails = $product->attributeValues->first();
        }

        return view('admin.product-edit', compact(
            'product',
            'categories',
            'productAttributes',
            'hasVariant',
            'variantDetails'
        ));
    }



    public function product_update(Request $request)
    {
        $product = Product::findOrFail($request->input('id'));
    
        // Determine if product has variants
        $hasVariant = $request->has('variant_name') &&
            is_array($request->variant_name) &&
            count(array_filter($request->variant_name)) > 0;
    
        // Validation
        $request->validate([
            'name' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'price' => $hasVariant ? 'nullable' : 'required|numeric',
            'quantity' => $hasVariant ? 'nullable' : 'required|integer|min:0',
            'featured' => 'required|boolean',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'sex' => 'required|in:male,female,all',
            'category_id' => 'required|integer|exists:categories,id',
            'reorder_quantity' => 'required|integer|min:0',
            'outofstock_quantity' => 'required|integer|min:0',
        ]);

        $previousStockStatus = $product->stock_status;
        $previousTotalQuantity = $product->quantity ?? $product->attributeValues->sum('quantity');
    
        // Update product fields
        $product->name = $request->name;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->price = $hasVariant ? null : $request->price;
        $product->quantity = $hasVariant ? null : $request->quantity;
        $product->sex = $request->sex;
        $product->featured = $request->featured;
        $product->category_id = $request->category_id;
        $product->reorder_quantity = $request->reorder_quantity;
        $product->outofstock_quantity = $request->outofstock_quantity;
    
        $current_timestamp = Carbon::now()->timestamp;
    
        // Stock Status Logic
        $totalQuantity = $product->quantity ?? $product->attributeValues->sum('quantity');
    
        if ($totalQuantity > $product->reorder_quantity) {
            $product->stock_status = 'instock';
        } elseif ($totalQuantity <= $product->reorder_quantity && $totalQuantity > $product->outofstock_quantity) {
            $product->stock_status = 'reorder';
        } else {
            $product->stock_status = 'outofstock';
        }
    
        // Handle image upload
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
                File::delete(public_path('uploads/products') . '/' . $product->image);
            }
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailsImage($image, $imageName);
            $product->image = $imageName;
        }
    
        // Handle gallery images
        if ($request->hasFile('images')) {
            $gallery_arr = [];
            foreach (explode(",", $product->images) as $ofile) {
                if (File::exists(public_path('uploads/products') . '/' . $ofile)) {
                    File::delete(public_path('uploads/products') . '/' . $ofile);
                }
            }
            foreach ($request->file('images') as $index => $file) {
                $gfilename = $current_timestamp . "-" . ($index + 1) . "." . $file->extension();
                $this->GenerateProductThumbnailsImage($file, $gfilename);
                $gallery_arr[] = $gfilename;
            }
            $product->images = implode(',', $gallery_arr);
        }
    
        $product->save();
    
        // Handle variants
        if ($hasVariant) {
            $product->attributeValues()->delete();
    
            $attributeValues = [];
            foreach ($request->variant_name as $index => $name) {
                $attributeValues[] = [
                    'product_id' => $product->id,
                    'product_attribute_id' => $request->product_attribute_id[$index],
                    'value' => $name,
                    'price' => $request->variant_price[$index],
                    'quantity' => $request->variant_quantity[$index],
                ];
            }
    
            foreach ($attributeValues as $value) {
                ProductAttributeValue::updateOrCreate(
                    [
                        'product_id' => $value['product_id'],
                        'product_attribute_id' => $value['product_attribute_id'],
                        'value' => $value['value']
                    ],
                    $value
                );
            }
    
            $variantTotalQuantity = collect($attributeValues)->sum('quantity');
    
            // Stock status based on variant quantities
            if ($variantTotalQuantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($variantTotalQuantity <= $product->reorder_quantity && $variantTotalQuantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }
    
            $product->save();
        }

        if ($product->stock_status === 'instock' && $previousStockStatus !== 'instock') {
            $users = User::where('utype', 'USR')->get();
            $message = "Good news! The product {$product->name} is now back in stock.";
            Notification::send($users, new StockUpdate($product, $message));
        }
    
        return redirect()->route('admin.products')->with('status', 'Product has been updated successfully!');
    }
    




    public function GenerateProductThumbnailsImage($image, $imageName)
    {

        try {

            $destinationPathThumbnail = public_path('uploads/products/thumbnails');
            $destinationPath = public_path('uploads/products');
            $img = Image::read($image->getRealPath());
            $img->cover(689, 689, "center");
            $img->resize(689, 689, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $imageName);

            $img->resize(204, 204, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPathThumbnail . '/' . $imageName);

            \Log::info('Image saved successfully: ' . $imageName);
        } catch (\Exception $e) {
            \Log::error('Image processing failed: ' . $e->getMessage());
        }
    }

    public function archivedProducts($id)
    {
        $product = Product::findOrFail($id);
        $product->archived = 1;
        $product->archived_at = \Carbon\Carbon::now();
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product archived successfully!');
    }

    public function restoreProducts(Request $request)
    {
        $productIds = $request->input('ids');
        Product::whereIn('id', $productIds)->update(['archived' => 0]);

        return response()->json(['status' => 'Products restored successfully!']);
    }

    public function showArchivedProducts()
    {
        $archivedProducts = Product::archived()->paginate(10);

        foreach ($archivedProducts as $product) {
            if ($product->archived_at) {
                $product->archived_at = Carbon::parse($product->archived_at);
            }
        }

        return view('admin.archived-products', compact('archivedProducts'));
    }
    public function prod_attributes()
    {
        $attributes = ProductAttribute::orderBy('id', 'DESC')->paginate(10);
        return view('admin.product-attributes', compact('attributes'));
    }

    public function prod_attribute_add()
    {
        return view('admin.product-attribute-add');
    }

    public function prod_attribute_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_attributes,name',
        ]);

        $attribute = new ProductAttribute();
        $attribute->name = $request->name;
        $attribute->save();

        return redirect()->route('admin.product-attributes')->with('status', 'Product attribute added successfully!');
    }


    public function product_attribute_edit($id)
    {
        $attribute =  ProductAttribute::find($id);
        return view('admin.product-attribute-edit', compact('attribute'));
    }

    public function product_attribute_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $attribute = ProductAttribute::find($request->id);
        $attribute->name = $request->name;
        $attribute->save();

        return redirect()->route('admin.product-attributes')->with('status', 'Product attribute updated successfully!');
    }

    public function product_attribute_delete($id)
    {
        $attribute = ProductAttribute::find($id);
        $attribute->delete();
        return redirect()->route('admin.product-attributes')->with('status', 'Product Variant has been deleted successfully!');
    }


    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }



    public function order_details($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();
        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }

    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);

        $order->status = $request->order_status;

        if ($request->order_status == 'pickedup') {
            $order->picked_up_date = Carbon::now();
        } else if ($request->order_status == 'canceled') {
            $order->canceled_date = Carbon::now();
        }
        $order->save();
        if ($request->order_status == 'pickedup') {
            $transaction = Transaction::where('order_id', $request->order_id)->first();


            $transaction->status = "approved";
            $transaction->save();
        }

        return back()->with("status", "Status changed successfully!")
            ->with("disabled", true);
    }


    // Sliders Page

    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }

    public function slide_add()
    {

        return view('admin.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' =>  'required',
            'title' =>  'required',
            'subtitle' =>  'required',
            'link' =>  'required',
            'status' =>  'required',
            'image' =>  'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateSlideThumbnailsImage($image, $file_name);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide   added successfully!');
    }

    public function GenerateSlideThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->getRealPath());
        $img->cover(1920, 1080, "top");
        $img->resize(1920, 1080, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }


    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    public function slide_update(Request $request)
    {
        $request->validate([
            'tagline' =>  'required',
            'title' =>  'required',
            'subtitle' =>  'required',
            'link' =>  'required',
            'status' =>  'required',
            'image' =>  'mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/slides') . '/' . $slide->image)) {
                File::delete(public_path('uploads/slides') . '/' . $slide->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->GenerateSlideThumbnailsImage($image, $file_name);
            $slide->image = $file_name;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide updated successfully!');
    }


    public function slide_delete($id)
    {
        $slide = Slide::find($id);
        if (File::exists(public_path('uploads/slides') . '/' . $slide->image)) {
            File::delete(public_path('uploads/slides') . '/' . $slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Slide has been deleted successfully!');
    }

    // Contact PAGE
    public function contacts()
    {
        $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.contacts', compact('contacts'));
    }
    public function contact_delete($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route(route: 'admin.contacts')->with('status', 'Contact has been deleted successfully !');
    }

    public function contact_reply(Request $request, $id)
    {
        // Find the contact record by its ID
        $contact = Contact::find($id);
        if (!$contact) {
            // Handle the case where the contact doesn't exist
            return redirect()->route('admin.contacts')->with('error', 'Contact not found.');
        }

        // Validate the reply message input
        $validated = $request->validate([
            'replyMessage' => 'required|string',
        ]);

        // Save the admin's reply to the database
        $reply = new ContactReplies();
        $reply->contact_id = $contact->id;
        $reply->admin_reply = $request->input('replyMessage');
        $reply->admin_id = Auth::id();// Assuming the admin is logged in
        $reply->save();

        // Optionally, send an email to the user
        Mail::to($contact->email)->send(new ReplyToContact($contact, $request->input('replyMessage')));

        // Redirect back with a success message
        return redirect()->route('admin.contacts')->with('status', 'Reply sent successfully!');
    }



    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
    
        return response()->json([
            'status' => 'success',
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }
    

    public function markMultipleAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids'); // Expecting an array of IDs
        if (!$notificationIds || empty($notificationIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No notification IDs provided.',
            ], 400);
        }
    
        // Mark notifications as read
        $notifications = Auth::user()->notifications()->whereIn('id', $notificationIds)->get();
    
        if ($notifications->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No matching notifications found.',
            ], 404);
        }
    
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
    
        return response()->json([
            'status' => 'success',
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }
    
    public function unreadCount()
    {
        return response()->json([
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }
    

    public function latest()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(10) // Fetch the latest 10 notifications
            ->get();

        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'message' => $notification->data['message'] ?? 'No message available',
                'icon' => match ($notification->type) {
                    'App\\Notifications\\LowStockNotification' => 'fa-solid fa-box',
                    'App\\Notifications\\ContactReceivedMessage' => 'fas fa-envelope',
                    default => 'fas fa-bell',
                },
                'redirect_route' => match ($notification->type) {
                    'App\\Notifications\\LowStockNotification' => route('admin.products'),
                    'App\\Notifications\\ContactReceivedMessage' => route('admin.contacts'),
                    default => '#',
                },
                'created_at' => $notification->created_at->diffForHumans(),
                'read_at' => $notification->read_at,
            ];
        });

        return response()->json([
            'unreadCount' => $user->unreadNotifications->count(),
            'notifications' => $formattedNotifications,
        ]);
    }

    public function deleteMultipleNotifications(Request $request)
    {
        $notificationIds = $request->input('notification_ids');
        Auth::user()->notifications()->whereIn('id', $notificationIds)->delete();
    
        return response()->json([
            'status' => 'success',
        ]);
    }
    // Reports Page
    public function generateReport(Request $request)
    {
        $currentDate = Carbon::now(); // Define the current date here
        $currentYear = $currentDate->year;
        $currentMonthId = $currentDate->month;

        // Get selected year and month from request, default to current year/month
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonthId = $request->input('month', $currentMonthId);

        // Fetch available months and the selected month
        $availableMonths = MonthName::orderBy('id')->get();
        $selectedMonth = $availableMonths->firstWhere('id', $selectedMonthId);

        if (!$selectedMonth) {
            $selectedMonth = $availableMonths->first();
            $selectedMonthId = $selectedMonth->id;
        }

        // Calculate the start and end of the selected month
        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Fetch the latest 10 orders
        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

        // Fetch dashboard data for the selected month and year
        $dashboardDatas = DB::select("SELECT 
                                        SUM(total) AS TotalAmount,
                                        SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                                        SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                                        SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                                        COUNT(*) AS Total,
                                        SUM(IF(status = 'reserved', 1, 0)) AS TotalReserved,
                                        SUM(IF(status = 'pickedup', 1, 0)) AS TotalPickedUp, 
                                        SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
                                      FROM Orders
                                      WHERE created_at BETWEEN ? AND ?", [$startOfMonth, $endOfMonth]);

        // Weekly data for the selected month
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            // Ensure the start and end of the week don't exceed the month boundaries
            if ($startOfWeek->lt($startOfMonth)) {
                $startOfWeek = $startOfMonth;
            }
            if ($endOfWeek->gt($endOfMonth)) {
                $endOfWeek = $endOfMonth;
            }

            // Add valid week ranges
            if ($startOfWeek->lte($endOfMonth)) {
                $weekRanges[$week] = [$startOfWeek, $endOfWeek];
            }
        }

        // Fetch totals for each week
        $totalAmounts = [];
        $reservationAmounts = [];
        $pickedUpAmounts = [];
        $canceledAmounts = [];

        foreach ($weekRanges as $week => [$startOfSelectedWeek, $endOfSelectedWeek]) {
            // Fetch total amounts for the week
            $dashboardData = DB::select(
                "SELECT 
                                            SUM(total) AS TotalAmount,
                                            SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                                            SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                                            SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                                        FROM Orders
                                        WHERE created_at BETWEEN ? AND ?",
                [$startOfSelectedWeek, $endOfSelectedWeek]
            )[0];

            $totalAmounts[$week] = $dashboardData->TotalAmount ?? 0;
            $reservationAmounts[$week] = $dashboardData->TotalReservedAmount ?? 0;
            $pickedUpAmounts[$week] = $dashboardData->TotalPickedUpAmount ?? 0;
            $canceledAmounts[$week] = $dashboardData->TotalCanceledAmount ?? 0;
        }

        // Prepare Weekly Data for View
        $AmountW = implode(',', $totalAmounts);
        $ReservationAmountW = implode(',', $reservationAmounts);
        $PickedUpAmountW = implode(',', $pickedUpAmounts);
        $CanceledAmountW = implode(',', $canceledAmounts);

        // Calculate overall totals for weekly data display
        $TotalAmountW = array_sum($totalAmounts);
        $TotalReservedAmountW = array_sum($reservationAmounts);
        $TotalPickedUpAmountW = array_sum($pickedUpAmounts);
        $TotalCanceledAmountW = array_sum($canceledAmounts);

        // Monthly data for the selected year
        $monthlyDatas = DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
                                        IFNULL(D.TotalAmount, 0) AS TotalAmount,
                                        IFNULL(D.TotalReservedAmount, 0) AS TotalReservedAmount,
                                        IFNULL(D.TotalPickedUpAmount, 0) AS TotalPickedUpAmount,
                                        IFNULL(D.TotalCanceledAmount, 0) AS TotalCanceledAmount 
                                     FROM month_names M
                                     LEFT JOIN (
                                         SELECT 
                                             MONTH(created_at) AS MonthNo,
                                             SUM(total) AS TotalAmount,
                                             SUM(IF(status='reserved', total, 0)) AS TotalReservedAmount,
                                             SUM(IF(status='pickedup', total, 0)) AS TotalPickedUpAmount,
                                             SUM(IF(status='canceled', total, 0)) AS TotalCanceledAmount
                                         FROM Orders 
                                         WHERE YEAR(created_at) = ? 
                                         GROUP BY MONTH(created_at)
                                     ) D ON D.MonthNo = M.id
                                     ORDER BY M.id", [$selectedYear]);

        // Prepare Monthly Data for View
        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $ReservationAmountM = implode(',', collect($monthlyDatas)->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountM = implode(',', collect($monthlyDatas)->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        // Calculate overall totals for monthly data display
        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalReservedAmount = collect($monthlyDatas)->sum('TotalReservedAmount');
        $TotalPickedUpAmount = collect($monthlyDatas)->sum('TotalPickedUpAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        // Calculate the range of years to show in the dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        // New Code (Adding daily data)
        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();
        $selectedWeekId = $request->input('week', $currentDate->weekOfMonth);

        $selectedWeek = $availableWeeks->firstWhere('week_number', $selectedWeekId);
        if (!$selectedWeek) {
            $selectedWeek = $availableWeeks->first();
            $selectedWeekId = $selectedWeek->week_number;
        }

        // Define the start and end of the selected week
        if (array_key_exists($selectedWeekId, $weekRanges)) {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[$selectedWeekId];
        } else {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[1]; // Default to week 1
        }

        // Query for daily data within the selected week, grouped by day
        $dailyDatasRaw = DB::select(
            "SELECT DAYOFWEEK(created_at) AS DayNo,
                                        DAYNAME(created_at) AS DayName,
                                        SUM(total) AS TotalAmount,
                                        SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                                        SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                                        SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                                       FROM Orders 
                                       WHERE created_at BETWEEN ? AND ?
                                       GROUP BY DAYOFWEEK(created_at), DAYNAME(created_at)
                                       ORDER BY DayNo",
            [$startOfSelectedWeek, $endOfSelectedWeek]
        );

        // Define the desired order of days
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Create a map of DayName to daily data
        $dailyDataMap = collect($dailyDatasRaw)->keyBy('DayName');

        // Reorder the daily data from Monday to Sunday, filling missing days with zeros
        $sortedDailyDatas = collect($daysOfWeek)->map(function ($day) use ($dailyDataMap) {
            if ($dailyDataMap->has($day)) {
                return $dailyDataMap->get($day);
            } else {
                return (object)[
                    'DayNo' => null,
                    'DayName' => $day,
                    'TotalAmount' => 0,
                    'TotalReservedAmount' => 0,
                    'TotalPickedUpAmount' => 0,
                    'TotalCanceledAmount' => 0,
                ];
            }
        });

        // Prepare Daily Data for View
        $AmountD = implode(',', $sortedDailyDatas->pluck('TotalAmount')->toArray());
        $ReservationAmountD = implode(',', $sortedDailyDatas->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountD = implode(',', $sortedDailyDatas->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountD = implode(',', $sortedDailyDatas->pluck('TotalCanceledAmount')->toArray());

        $TotalAmountD = $sortedDailyDatas->sum('TotalAmount');
        $TotalReservedAmountD = $sortedDailyDatas->sum('TotalReservedAmount');
        $TotalPickedUpAmountD = $sortedDailyDatas->sum('TotalPickedUpAmount');
        $TotalCanceledAmountD = $sortedDailyDatas->sum('TotalCanceledAmount');

        // Return View with all data
        $pageTitle = 'Reports';
        return view('admin.reports', compact(
            'orders',
            'dashboardDatas',
            'AmountM',
            'ReservationAmountM',
            'PickedUpAmountM',
            'CanceledAmountM',
            'TotalAmount',
            'TotalReservedAmount',
            'TotalPickedUpAmount',
            'TotalCanceledAmount',
            'AmountW',
            'ReservationAmountW',
            'PickedUpAmountW',
            'CanceledAmountW',
            'TotalAmountW',
            'TotalReservedAmountW',
            'TotalPickedUpAmountW',
            'TotalCanceledAmountW',
            'selectedMonth',
            'selectedYear',
            'availableMonths',
            'yearRange',
            'sortedDailyDatas',
            'AmountD',
            'ReservationAmountD',
            'PickedUpAmountD',
            'CanceledAmountD',
            'TotalAmountD',
            'TotalReservedAmountD',
            'TotalPickedUpAmountD',
            'TotalCanceledAmountD',
            'selectedWeekId',
            'availableWeeks',
            'pageTitle'
        ));
    }

    public function generateProduct(Request $request)
    {
        // Get the current year and month
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);

        // Most frequent products data - simplified query for testing
        $mostFrequentProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_orders')
            ->limit(5)
            ->get();

        // Least bought products data - simplified query for testing
        $leastBoughtProducts = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->select(
                'products.name',
                DB::raw('COUNT(order_items.id) as total_orders')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_orders')
            ->limit(5)
            ->get();

        // Prepare data for charts
        $mostFrequentLabels = $mostFrequentProducts->pluck('name');
        $mostFrequentData = $mostFrequentProducts->pluck('total_orders');
        $leastBoughtLabels = $leastBoughtProducts->pluck('name');
        $leastBoughtData = $leastBoughtProducts->pluck('total_orders');

        // Available months and year range for the form
        $availableMonths = DB::table('month_names')->get();
        $yearRange = range($currentYear, $currentYear - 10);

        return view('admin.report-product', compact(
            'mostFrequentLabels',
            'mostFrequentData',
            'leastBoughtLabels',
            'leastBoughtData',
            'availableMonths',
            'yearRange',
            'selectedMonth',
            'selectedYear'
        ));
    }

    public function generateUser(Request $request)
    {
        // Initialize variables for the existing month/year report
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);

        // Initialize variables for the new date range report
        $newUsersCount = null;
        $newUsers = null;
        $startDate = null;
        $endDate = null;
        $chartData = null;

        // Check if the date range form was submitted
        if ($request->isMethod('POST') && $request->has(['start_date', 'end_date'])) {
            // Validate the request
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            // Retrieve input and parse dates
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

            // Query the database for new users within the date range
            $newUsersCount = User::whereBetween('created_at', [$startDate, $endDate])->count();

            // Aggregate user registrations by day
            $userRegistrations = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // Prepare data for ApexCharts
            $chartData = [
                'dates' => $userRegistrations->pluck('date')->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                })->toArray(),
                'counts' => $userRegistrations->pluck('count')->toArray(),
            ];

            // Retrieve user details
            $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->get();
        }

        // Existing recent users
        $recentUsers = User::orderBy('created_at', 'DESC')->take(10)->get();

        // Monthly user data for the selected year
        $userRegistrations = DB::select("
            SELECT COUNT(*) AS TotalUsers, MONTH(created_at) AS MonthNo
            FROM users
            WHERE YEAR(created_at) = ?
            GROUP BY MonthNo
            ORDER BY MonthNo", [$selectedYear]);

        $monthlyData = array_fill(1, 12, 0);
        foreach ($userRegistrations as $data) {
            $monthlyData[$data->MonthNo] = $data->TotalUsers;
        }
        $userRegistrationsByMonth = implode(',', $monthlyData);

        // Weekly user data
        $weeklyData = array_fill(1, 6, 0);
        $userCounts = DB::table('users')
            ->selectRaw('WEEK(created_at, 1) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at) - 1 DAY), 1) + 1 as week_number')
            ->selectRaw('COUNT(*) as count')
            ->whereYear('created_at', $selectedYear)
            ->whereMonth('created_at', $selectedMonth)
            ->groupBy('week_number')
            ->get();

        foreach ($userCounts as $count) {
            $weekIndex = $count->week_number;
            if (isset($weeklyData[$weekIndex])) {
                $weeklyData[$weekIndex] = $count->count;
            }
        }
        $weeklyChartData = implode(',', $weeklyData);

        // Daily user data
        $dailyData = array_fill(0, 7, 0);
        $dailyCounts = DB::table('users')
            ->selectRaw('DAYOFWEEK(created_at) as day_of_week')
            ->selectRaw('COUNT(*) as count')
            ->whereYear('created_at', $selectedYear)
            ->whereMonth('created_at', $selectedMonth)
            ->groupBy('day_of_week')
            ->get();

        foreach ($dailyCounts as $count) {
            $dayIndex = $count->day_of_week - 2;
            if (isset($dailyData[$dayIndex])) {
                $dailyData[$dayIndex] = $count->count;
            }
        }
        $dailyChartData = implode(',', $dailyData);

        // Available months and year range for the form
        $availableMonths = DB::table('month_names')->get();
        $yearRange = range($currentYear, $currentYear - 10);

        // Page Title
        $pageTitle = 'User Registrations Report';

        // Return the view with all necessary data
        return view('admin.report-user', compact(
            'recentUsers',
            'userRegistrationsByMonth',
            'weeklyChartData',
            'dailyChartData',
            'availableMonths',
            'yearRange',
            'selectedMonth',
            'selectedYear',
            'pageTitle',
            'newUsersCount',
            'newUsers',
            'startDate',
            'endDate',
            'chartData'
        ))->with('showChart', true);
    }

    public function generateInventory(Request $request)
    {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
            'today'         => 'nullable|boolean',
            'stock_status'  => 'nullable|string|in:instock,outofstock,reorder',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if "Today" filter is applied
        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            // Get start_date and end_date from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        // Additional validation: Ensure end_date is after or equal to start_date
        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->back()->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])->withInput();
        }

        // Build the query for products
        $productsQuery = Product::with(['category', 'orderItems', 'attributeValues']);

        // Apply date filter if dates are provided
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Filter products based on updated_at date
            $productsQuery->whereBetween('updated_at', [$start, $end]);
        }

        // Get the products
        $products = $productsQuery->get();

        // Apply stock status filter if provided
        $stockStatus = $request->input('stock_status');

        if ($stockStatus) {
            $products = $products->filter(function ($product) use ($stockStatus) {
                $currentStock = $product->attributeValues->isNotEmpty()
                    ? $product->attributeValues->sum('quantity')
                    : $product->current_stock;

                if ($stockStatus == 'instock') {
                    return $currentStock > $product->reorder_quantity;
                } elseif ($stockStatus == 'outofstock') {
                    return $currentStock <= $product->outofstock_quantity;
                } elseif ($stockStatus == 'reorder') {
                    return $currentStock > $product->outofstock_quantity && $currentStock <= $product->reorder_quantity;
                }

                return true;
            });
        }

        // Paginate the results manually
        $page = $request->input('page', 1);
        $perPage = 20;
        $total = $products->count();
        $products = $products->forPage($page, $perPage);

        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $products,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.report-inventory', compact('products', 'startDate', 'endDate'));
    }




    public function generateBillingStatement($orderId)
    {
        $order = Order::with(['user', 'orderItems.product'])->findOrFail($orderId);
        return view('admin.report-statement', compact('order'));
    }

    public function listBillingStatements(Request $request)
    {
        // Validate the date inputs
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'today' => 'nullable|boolean',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if "Today" filter is applied
        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            // Get start_date and end_date from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        // Additional validation: Ensure end_date is after or equal to start_date
        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->back()->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])->withInput();
        }

        // Build the query for orders
        $ordersQuery = Order::with('user');

        // Apply date filter if dates are provided
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $ordersQuery->whereBetween('created_at', [$start, $end]);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->get();

        return view('admin.report-statements', compact('orders', 'startDate', 'endDate'));
    }

    // DomPDF for Reports Products Page
    public function downloadBillingStatements(Request $request)
    {
        // Validate the date inputs
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'today' => 'nullable|boolean',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->route('admin.report-statements')
                ->withErrors($validator)
                ->withInput();
        }

        // Check if "Today" filter is applied
        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            // Get start_date and end_date from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        // Additional validation: Ensure end_date is after or equal to start_date
        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->route('admin.report-statements')
                ->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])
                ->withInput();
        }

        // Build the query for orders
        $ordersQuery = Order::with('user');

        // Apply date filter if dates are provided
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $ordersQuery->whereBetween('created_at', [$start, $end]);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->get();

        // Load the view for PDF rendering
        $pdf = PDF::loadView('admin.pdf-billing', [
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        // Return the generated PDF for download
        return $pdf->download('billing_statements.pdf');
    }

    public function downloadPdf(Request $request)
    {
        // Retrieve base64 image data from the request
        $monthlySalesImg = $request->input('monthly_sales_img');
        $weeklySalesImg = $request->input('weekly_sales_img');
        $dailySalesImg = $request->input('daily_sales_img');

        // Fetch other report data as necessary
        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

        $pdf = PDF::loadView('admin.pdf-reports', [
            'monthlySalesImg' => $monthlySalesImg,
            'weeklySalesImg' => $weeklySalesImg,
            'dailySalesImg' => $dailySalesImg,
            'orders' => $orders,

        ])->setPaper('A4', 'portrait');

        return $pdf->download('sales_report.pdf');
    }

    public function downloadProduct(Request $request)
    {
        // Get the data again for the PDF (same logic as in generateProduct)
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);

        $mostFrequentProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_orders')
            ->limit(5)
            ->get();

        $leastBoughtProducts = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->select(
                'products.name',
                DB::raw('COUNT(order_items.id) as total_orders')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_orders')
            ->limit(5)
            ->get();

        // Prepare data for the PDF view
        $mostFrequentLabels = $mostFrequentProducts->pluck('name');
        $mostFrequentData = $mostFrequentProducts->pluck('total_orders');
        $leastBoughtLabels = $leastBoughtProducts->pluck('name');
        $leastBoughtData = $leastBoughtProducts->pluck('total_orders');

        // Initialize DomPDF
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set("isHtml5ParserEnabled", true);
        $dompdf->setOptions($options);

        // Load HTML content
        $html = view('admin.pdf-products', compact(
            'mostFrequentLabels',
            'mostFrequentData',
            'leastBoughtLabels',
            'leastBoughtData'
        ))->render();

        // Load HTML and render PDF
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Output the generated PDF to browser
        return $dompdf->stream('product-report.pdf');
    }

    public function downloadUserReportPdf(Request $request)
    {
        // Get the base64 images
        $monthlyChartImage = $request->input('monthlyChartImage');
        $weeklyChartImage = $request->input('weeklyChartImage');
        $dailyChartImage = $request->input('dailyChartImage');

        // Prepare data for the PDF view
        $pdfData = [
            'recentUsers' => User::orderBy('created_at', 'DESC')->take(10)->get(),
            'userRegistrationsByMonth' => $request->input('userRegistrationsByMonth'),
            'weeklyChartData' => $request->input('weeklyChartData'),
            'dailyChartData' => $request->input('dailyChartData'),
            'monthlyChartImage' => $monthlyChartImage,
            'weeklyChartImage' => $weeklyChartImage,
            'dailyChartImage' => $dailyChartImage,
            'selectedMonth' => $request->input('selectedMonth'),
            'selectedYear' => $request->input('selectedYear'),
        ];

        // Generate PDF
        $pdf = PDF::loadView('admin.pdf-user', $pdfData)
            ->setPaper('a4', 'portrait');


        // Return the PDF download
        return $pdf->download('user_report_' . $request->input('selectedYear') . '_' . $request->input('selectedMonth') . '.pdf');
    }

    public function downloadInventoryReportPdf(Request $request)
    {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
            'today'         => 'nullable|boolean',
            'stock_status'  => 'nullable|string|in:instock,outofstock,reorder',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->route('admin.report-inventory')
                ->withErrors($validator)
                ->withInput();
        }

        // Check if "Today" filter is applied
        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            // Get start_date and end_date from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        // Additional validation: Ensure end_date is after or equal to start_date
        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->route('admin.report-inventory')
                ->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])
                ->withInput();
        }

        // Build the query for products
        $productsQuery = Product::with(['category', 'orderItems', 'attributeValues']);

        // Apply date filter if dates are provided
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Filter products based on updated_at date
            $productsQuery->whereBetween('updated_at', [$start, $end]);
        }

        // Get the products
        $products = $productsQuery->get();

        // Apply stock status filter if provided
        $stockStatus = $request->input('stock_status');

        if ($stockStatus) {
            $products = $products->filter(function ($product) use ($stockStatus) {
                $currentStock = $product->attributeValues->isNotEmpty()
                    ? $product->attributeValues->sum('quantity')
                    : $product->current_stock;

                if ($stockStatus == 'instock') {
                    return $currentStock > $product->reorder_quantity;
                } elseif ($stockStatus == 'outofstock') {
                    return $currentStock <= $product->outofstock_quantity;
                } elseif ($stockStatus == 'reorder') {
                    return $currentStock > $product->outofstock_quantity && $currentStock <= $product->reorder_quantity;
                }

                return true;
            });
        }

        // Load the view for PDF rendering
        $pdfView = view('admin.pdf-inventory-report', compact('products', 'startDate', 'endDate'))->render();

        // DomPDF options to improve performance
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        // Initialize DomPDF and load the HTML
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfView);

        // Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Download the generated PDF file
        return $dompdf->stream('inventory_report.pdf', ['Attachment' => true]);
    }




    public function filter(Request $request)
    {
        $yearLevel = $request->input('year_level');
        $department = $request->input('department');

        $query = User::query();

        if ($yearLevel) {
            $query->where('year_level', $yearLevel);
        }

        if ($department) {
            $query->where('department', $department); // Assuming department represents colleges
        }

        $users = $query->get();

        return response()->json($users);
    }


    public function searchproduct(Request $request)
    {
        $query = strtolower($request->input('query'));

        // Check if the query matches certain keywords and redirect accordingly

        //product
        if (str_contains(strtolower($query), 'add product')) {
            return redirect()->route('admin.product.add');
        } elseif (str_contains(strtolower($query), 'products view')) {
            return redirect()->route('admin.products');
        } elseif (str_contains(strtolower($query), 'add product attributes')) {
            return redirect()->route('admin.product-attribute-add');
        } elseif (str_contains(strtolower($query), 'view product attributes')) {
            return redirect()->route('admin.product-attributes');
        } elseif (str_contains(strtolower($query), 'addcategory')) {
            return redirect()->route('admin.category.add');
        } elseif (str_contains(strtolower($query), 'categories')) {
            return redirect()->route('admin.categories');
        } elseif (str_contains(strtolower($query), 'users')) {
            return redirect()->route('admin.users');
        } elseif (str_contains(strtolower($query), 'order')) {
            return redirect()->route('admin.orders');

            //product report

        } elseif (str_contains(strtolower($query), 'product report')) {
            return redirect()->route('admin.report-product');
        } elseif (str_contains(strtolower($query), 'sales products')) {
            return redirect()->route('admin.reports');
        } elseif (str_contains(strtolower($query), 'user report')) {
            return redirect()->route('admin.report-user');
        } elseif (str_contains(strtolower($query), 'inventory')) {
            return redirect()->route('admin.report-inventory');
        } elseif (str_contains(strtolower($query), 'statements')) {
            return redirect()->route('admin.report-statements');


            //rentals
        } elseif (str_contains(strtolower($query), 'add rental')) {
            return redirect()->route('admin.rental.add');
        } elseif (str_contains(strtolower($query), 'rentals view')) {
            return redirect()->route('admin.rentals');
        } elseif (str_contains(strtolower($query), 'rental reservation')) {
            return redirect()->route('admin.reservation');


            //rental report

        } elseif (str_contains(strtolower($query), 'sales rental')) {
            return redirect()->route('admin.rentals_reports');
        } elseif (str_contains(strtolower($query), 'messages')) {
            return redirect()->route('admin.contacts');
        } elseif (str_contains(strtolower($query), 'slides')) {
            return redirect()->route('admin.slides');
        } elseif (str_contains(strtolower($query), 'Reservation Reports')) {
            return redirect()->route('admin.rentalsReportsName');
        }

        // Add more conditions as needed
        return back()->with('error', 'No matching results found.');
    }

    public function updateStatus(Request $request,  Reservation $reservation)
    {   

        $reservation->update([
            'payment_status' => $request->input('payment_status'),
            'rent_status' => $request->input('rent_status'),
        ]);
    
        
        // Validate the incoming request
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'rent_status' => 'required|in:pending,reserved,completed,canceled',
        ]);

        try {
            // Find the reservation by ID
            $reservation = Reservation::findOrFail($request->reservation_id);

            // Update the status
            $reservation->rent_status = $request->rent_status;
            $reservation->save();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Reservation status updated successfully!',
            ]);
        } catch (\Exception $e) {
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the reservation status.',
            ], 500);
        }
    }
    
    
    // public function filterReservations(Request $request)
    // {
    //     $query = Reservation::with(['user', 'rental']);

    //     if ($request->filled('reservation_type')) {
    //         $query->whereHas('rental', function ($q) use ($request) {
    //             $q->where('name', $request->reservation_type);
    //         });
    //     }

    //     if ($request->filled('rent_status')) {
    //         $query->where('rent_status', $request->rent_status);
    //     }

    //     if ($request->filled('payment_status')) {
    //         $query->where('payment_status', $request->payment_status);
    //     }

    //     $reservations = $query->get();

    //     return response()->json($reservations);
    // }

    public function filterReservations(Request $request)
    {
        $query = Reservation::query();

        // Filter by Rent Status
        if ($request->filled('rent_status')) {
            $query->where('rent_status', $request->rent_status);
        }

        // Filter by Payment Status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by Rental Type
        if ($request->filled('rental_type')) {
            $query->whereHas('rental', function($q) use ($request) {
                $q->where('name', $request->rental_type);
            });
        }

        $reservations = $query->get();

        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('reservations.filtered', compact('reservations'))->render()
            ]);
        }

        // If not AJAX, return regular view
        return view('reservations.index', compact('reservations'));
    }


    public function updatePaymentStatus(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|exists:reservations,id',  // Ensure reservation exists
            'payment_status' => 'required|string',     // Ensure valid payment status
        ]);

        // Find the reservation by its ID
        $reservation = Reservation::find($request->id);

        if ($reservation) {
            // Update the payment status
            $reservation->payment_status = $request->payment_status;
            $reservation->save();

            // Return a success response
            return response()->json(['success' => true, 'message' => 'Payment status updated successfully.']);
        }

        // If the reservation is not found, return an error response
        return response()->json(['success' => false, 'message' => 'Reservation not found.']);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->get();

        return response()->json($results);
    }


    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }
    public function users_destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully');
    }

    public function filterOrders(Request $request)
    {
        $query = Order::query();

        // Apply filters
        if ($request->has('time_slot') && $request->time_slot != '') {
            $query->where('time_slot', $request->time_slot);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Fetch the filtered orders with the count of order items
        $orders = $query->withCount('orderItems')->get();

        return response()->json($orders); // Send the filtered orders as JSON response
    }


    public function order_filter(Request $request)
    {
        Log::info('Received filter request', [
            'time_slot' => $request->time_slot,
            'status' => $request->status
        ]);

        // Validate filters
        $validatedData = $request->validate([
            'time_slot' => 'nullable|string',
            'status' => 'nullable|string|in:reserved,pickedup,canceled'
        ]);

        $query = Order::query();

        if ($request->filled('time_slot')) {
            $query->where('time_slot', $request->time_slot);
            Log::info('Applied time slot filter', ['time_slot' => $request->time_slot]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
            Log::info('Applied status filter', ['status' => $request->status]);
        }

        $query->latest(); // Default sorting by most recent

        try {
            $orders = $query->paginate(10);
            Log::info('Filtered orders fetched successfully.', [
                'total_orders' => $orders->total(),
                'current_page' => $orders->currentPage()
            ]);

            return response()->json([
                'orders' => $orders->items(),
                'total' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching filtered orders', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while fetching orders.'], 500);
        }
    }

    public function users_edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user-edit', compact('user'));
    }

    
    public function users_update(Request $request, $id)
    {
        $request->validate([
            'phone_number' => 'nullable|string|max:15',
            'year_level' => 'nullable|string|max:10',
            'department' => 'nullable|string|max:50',
            'course' => 'nullable|string|max:50',
        ]);

        $user = User::findOrFail($id);
        $user->phone_number = $request->phone_number;
        $user->year_level = $request->year_level;
        $user->department = $request->department;
        $user->course = $request->course;

        $user->save();
        return redirect()->route('admin.users')->with('status', 'User has been updated successfully!');
    }

    public function users_add()
    {

        $users = User::all();

        return view("admin.user-add", compact('users'));
    }


    public function users_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable',
            'role' => 'required',
            'phone_number' => 'nullable',
            'year_level' => 'nullable',
            'department' => 'nullable',
            'course' => 'nullable',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make('defaultpassword');
        $user->role = $request->role;
        $user->phone_number = $request->phone_number;
        $user->year_level = $request->year_level;
        $user->department = $request->department;
        $user->course = $request->course;

        $user->save();
        return redirect()->route('admin.users')->with('status', 'User has been added successfully!');
    }

    public function searchProducts(Request $request)
{
    $query = $request->input('query');

    // Fetch products with their related data (including variants and categories)
    $products = Product::with(['category.parent', 'attributeValues.productAttribute'])
        ->where('name', 'LIKE', "%{$query}%")
        ->orWhereHas('category', function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%");
        })
        ->get();

    // Add total quantity, variant prices, and stock status to each product
    foreach ($products as $product) {
        $variantPrices = [];
        $totalQuantity = 0;

        // If the product has variants, calculate total quantity and variant prices
        if ($product->attributeValues->isNotEmpty()) {
            foreach ($product->attributeValues as $variant) {
                $variantPrices[] = [
                    'value' => $variant->value,
                    'price' => $variant->price
                ];
                $totalQuantity += $variant->quantity; // Sum of variant quantities
            }
        } else {
            // If no variants, use the product's base quantity and price
            $totalQuantity = $product->quantity;
            $variantPrices[] = [
                'value' => 'Base Price',
                'price' => $product->price
            ];
        }

        // Add the calculated data to the product
        $product->total_quantity = $totalQuantity;
        $product->variant_prices = $variantPrices;

        // Set stock status based on total quantity
        if ($totalQuantity == 0) {
            $product->stock_status = "Out of Stock";
            $product->badge_class = "badge-danger"; // Badge for out of stock
        } elseif ($totalQuantity <= $product->outofstock_quantity) {
            $product->stock_status = "Out of Stock"; // Out of stock level
            $product->badge_class = "badge-danger"; // Badge for out of stock
        } elseif ($totalQuantity <= $product->reorder_quantity) {
            $product->stock_status = "Reorder Level"; // Set reorder level if quantity is below reorder threshold
            $product->badge_class = "badge-warning"; // Badge for reorder level
        } else {
            $product->stock_status = "In Stock"; // This can be used if you have some threshold
            $product->badge_class = "badge-success"; // Badge for in stock
        }

        // Set the product price (either base price or first variant's price)
        $product->price = $product->attributeValues->isNotEmpty() ? $product->attributeValues->first()->price : $product->price;
    }

    return response()->json($products);
}




    // Rentals Page

    public function rentals()
    {
        $rentals = Rental::with('dormitoryRooms')->orderBy('created_at', 'DESC')->paginate(5);
        $dormitoryRooms = DormitoryRoom::all();
        return view('admin.rentals', compact('rentals', 'dormitoryRooms'));
    }


    public function rental_add()
    {

        return view('admin.rental-add');
    }

    public function rental_store(Request $request)
    {
        // Define rental names that require specific fields
        $priceRequiredNames = ['Male Dormitory', 'Female Dormitory', 'International House II'];
        $internalExternalRequiredNames = ['International Convention Center', 'Rolle Hall', 'Swimming Pool'];
        // dd($request->all());    
        try {
            $rules = [
                'name' => 'required|unique:rentals,name',
                'description' => 'required',
                'rules_and_regulations' => 'required|string',
                'capacity' => 'nullable|integer',
                'price' => [
                    'nullable',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request, $priceRequiredNames) {
                        if (in_array($request->name, $priceRequiredNames) && empty($value)) {
                            $fail('The price field is required for the selected rental type.');
                        }
                    },
                ],
                'internal_price' => [
                    'nullable',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request, $internalExternalRequiredNames) {
                        if (in_array($request->name, $internalExternalRequiredNames) && empty($value)) {
                            $fail('The internal price field is required for the selected rental type.');
                        }
                    },
                ],
                'external_price' => [
                    'nullable',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request, $internalExternalRequiredNames) {
                        if (in_array($request->name, $internalExternalRequiredNames) && empty($value)) {
                            $fail('The external price field is required for the selected rental type.');
                        }
                    },

                ],
                'exclusive_price' => [
                    'nullable',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request, $internalExternalRequiredNames) {
                        if (in_array($request->name, $internalExternalRequiredNames) && empty($value)) {
                            $fail('The exclusive price field is required for the selected rental type.');
                        }
                    },

                ],
                'status' => 'required|in:available,not available',
                'featured' => 'required|boolean',
                'image' => 'required|mimes:png,jpg,jpeg|max:2048',
                'requirements' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048',
                'images.*' => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
                'sex' => 'required|in:male,female,all', // Ensure the sex is required and valid
            ];

            if (in_array($request->name, ['Male Dormitory', 'Female Dormitory', 'International House II'])) {
                $rules['room_number'] = 'required|array';
                $rules['room_number.*'] = 'string|required';
                $rules['room_capacity'] = 'required|array';
                $rules['room_capacity.*'] = 'integer|required';
            } else {
                $rules['room_number'] = 'nullable|array';
                $rules['room_capacity'] = 'nullable|array';
            }

            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            Log::error('Validation failed for rental store: ', $e->validator->errors()->toArray());
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        // Initialize the Rental model
        $rental = new Rental();
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
        $rental->sex = $request->sex; // Store the selected sex

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





    public function GenerateRentalThumbnailsImage($image, $imageName)
    {
        $destinationPathThumbnail = public_path('uploads/rentals/thumbnails');
        $destinationPath = public_path('uploads/rentals');
        $img = Image::read($image->getRealPath());
        $img->cover(700, 700, "top");
        $img->resize(700, 700, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);

        $img->resize(204, 204, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail . '/' . $imageName);
    }

    //  RENTAL ADMIN EDIT FUNCTION
    public function rental_edit($id)
    {
        $rental = Rental::with('dormitoryRooms')->find($id);
        if (!$rental) {
            return redirect()->route('admin.rentals')->with('error', 'Rental not found.');
        }
        return view('admin.rental-edit', compact('rental'));
    }
    public function rental_update(Request $request)
    {
        // Define rental types that require specific fields
        $priceRequiredNames = ['Male Dormitory', 'Female Dormitory', 'International House II'];
        $internalExternalRequiredNames = ['International Convention Center', 'Rolle Hall', 'Swimming Pool'];

        // Validate the incoming request
        $rules = [
            'id' => 'required|exists:rentals,id',
            'name' => 'required',
            'description' => 'required',
            'rules_and_regulations' => 'required|string',
            'price' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($request, $priceRequiredNames) {
                    if (in_array($request->name, $priceRequiredNames) && empty($value)) {
                        $fail('The price field is required for the selected rental type.');
                    }
                },
            ],
            'internal_price' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($request, $internalExternalRequiredNames) {
                    if (in_array($request->name, $internalExternalRequiredNames) && empty($value)) {
                        $fail('The internal price field is required for the selected rental type.');
                    }
                },
            ],
            'external_price' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($request, $internalExternalRequiredNames) {
                    if (in_array($request->name, $internalExternalRequiredNames) && empty($value)) {
                        $fail('The external price field is required for the selected rental type.');
                    }
                },
            ],
            'exclusive_price' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->name === 'Swimming Pool' && empty($value)) {
                        $fail('The exclusive price is required for the Swimming Pool rental.');
                    }
                    if ($request->name !== 'Swimming Pool' && !empty($value)) {
                        $fail('The exclusive price field should not be filled for this rental type.');
                    }
                },
            ],

            'status' => 'required|in:available,not available',
            'featured' => 'required|boolean',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'requirements' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048',
            'images.*' => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
            'room_number' => 'required_if:name,Male Dormitory,Female Dormitory,International House II|array',
            'room_number.*' => 'required|string',
            'room_capacity' => 'required_if:name,Male Dormitory,Female Dormitory,International House II|array',
            'room_capacity.*' => 'required|integer',
            'start_date' => 'required_if:name,Male Dormitory,Female Dormitory,International House II|array',
            'start_date.*' => 'required|date',
            'end_date' => 'required_if:name,Male Dormitory,Female Dormitory,International House II|array',
            'end_date.*' => 'required|date|after_or_equal:start_date.*',
        ];

        $validatedData = $request->validate($rules);

        // Retrieve the existing Rental model
        $rental = Rental::findOrFail($request->id);

        // Update rental fields
        $rental->name = $request->name;
        $rental->slug = Str::slug($request->name);
        $rental->description = $request->description;
        $rental->rules_and_regulations = $request->rules_and_regulations;
        $rental->price = in_array($request->name, $priceRequiredNames) ? $request->price : null;
        $rental->internal_price = in_array($request->name, $internalExternalRequiredNames) ? $request->internal_price : null;
        $rental->external_price = in_array($request->name, $internalExternalRequiredNames) ? $request->external_price : null;
        $rental->exclusive_price = in_array($request->name, $internalExternalRequiredNames) ? $request->exclusive_price : null;

        // Ensure capacity is updated if applicable
        if (in_array($request->name, $internalExternalRequiredNames)) {
            $rental->capacity = $request->capacity;
        }

        $rental->status = $request->status;
        $rental->featured = $request->featured;
        $current_timestamp = Carbon::now()->timestamp;

        // Handle main image upload
        if ($request->hasFile('image')) {
            Log::info('Image file detected for rental update');
            if ($rental->image && File::exists(public_path('uploads/rentals/' . $rental->image))) {
                File::delete(public_path('uploads/rentals/' . $rental->image));
            }
            if ($rental->image && File::exists(public_path('uploads/rentals/thumbnails/' . $rental->image))) {
                File::delete(public_path('uploads/rentals/thumbnails/' . $rental->image));
            }

            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateRentalThumbnailsImage($image, $imageName);
            $rental->image = $imageName;
        }

        // Handle gallery images
        $gallery_arr = [];
        if ($request->hasFile('images')) {
            Log::info('Gallery images detected for rental update');
            if ($rental->images) {
                foreach (explode(",", $rental->images) as $ofile) {
                    if (File::exists(public_path('uploads/rentals/' . $ofile))) {
                        File::delete(public_path('uploads/rentals/' . $ofile));
                    }
                    if (File::exists(public_path('uploads/rentals/thumbnails/' . $ofile))) {
                        File::delete(public_path('uploads/rentals/thumbnails/' . $ofile));
                    }
                }
            }

            $counter = 1;
            foreach ($request->file('images') as $file) {
                $gfilename = $current_timestamp . "-" . $counter . "." . $file->getClientOriginalExtension();
                $this->GenerateRentalThumbnailsImage($file, $gfilename);
                $gallery_arr[] = $gfilename;
                $counter++;
            }
            $rental->images = implode(',', $gallery_arr);
        }

        // Handle Requirements upload
        if ($request->hasFile('requirements')) {
            Log::info('Requirements file detected for rental update');
            if ($rental->requirements && File::exists(public_path('uploads/rentals/files/' . $rental->requirements))) {
                File::delete(public_path('uploads/rentals/files/' . $rental->requirements));
            }

            $requirementsFile = $request->file('requirements');
            $requirementsFileName = $current_timestamp . '-requirements.' . $requirementsFile->getClientOriginalExtension();
            $requirementsFile->move(public_path('uploads/rentals/files'), $requirementsFileName);
            $rental->requirements = $requirementsFileName;
        }

        $rental->save();

        // Handle Dormitory Rooms Deletion
        if ($request->has('removed_rooms')) {
            $removedRoomIds = $request->removed_rooms;
            DormitoryRoom::whereIn('id', $removedRoomIds)->delete();
        }

        // Handle Dormitory Rooms Update or Creation
        if (in_array($rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II'])) {
            if ($request->has('room_number')) {
                foreach ($request->room_number as $index => $roomNumber) {
                    // Use `findOrNew` for existing rooms and new ones
                    $roomId = $request->room_id[$index] ?? null;
                    $dormitoryRoom = DormitoryRoom::findOrNew($roomId);

                    $dormitoryRoom->rental_id = $rental->id;
                    $dormitoryRoom->room_number = $roomNumber;
                    $dormitoryRoom->room_capacity = $request->room_capacity[$index];
                    $dormitoryRoom->start_date = $request->start_date[$index];
                    $dormitoryRoom->end_date = $request->end_date[$index];
                    $dormitoryRoom->save();
                }
            }
        }

        return redirect()->route('admin.rentals')->with('success', 'Rental updated successfully.');
    }

    




    public function rental_delete($id)
    {

        $rental = Rental::find($id);
        if (File::exists(public_path('uploads/rentals') . '/' . $rental->image)) {
            (File::delete(public_path('uploads/rentals') . '/' . $rental->image));
        }
        if (File::exists(public_path('uploads/rentals/thumbnails') . '/' . $rental->image)) {
            (File::delete(public_path('uploads/rentals/thumbnails') . '/' . $rental->image));
        }

        foreach (explode(",", $rental->images) as $ofile) {
            if (File::exists(public_path('uploads/rentals') . '/' . $ofile)) {
                File::delete(public_path('uploads/rentals') . '/' . $ofile);
            }

            if (File::exists(public_path('uploads/rentals/thumbails') . '/' . $ofile)) {
                File::delete(public_path('uploads/rentals/thumbails') . '/' . $ofile);
            }
        }

        $rental->delete();
        return redirect()->route('admin.rentals')->with('status', 'Rental has been deleted successfully !');
    }




    public function reservations(Request $request)
    {
        $query = Reservation::with('rental', 'user', 'dormitoryRoom')->orderBy('created_at', 'DESC');
    
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            })->orWhereHas('rental', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
    
        $reservations = $query->paginate(10)->withQueryString();
    
        $rentalNames = ['Male Dormitory', 'Female Dormitory', 'International House II'];
    
        $rentalIds = $reservations->pluck('rental_id')->unique()->toArray();
    
        $filteredRentalIds = Rental::whereIn('id', $rentalIds)
            ->whereIn('name', $rentalNames)
            ->pluck('id')->toArray();
    
        $availableRooms = DormitoryRoom::whereIn('rental_id', $filteredRentalIds)
            ->withCount([
                'reservations' => function ($query) {
                    $query->where('rent_status', 'reserved');
                }
            ])
            ->get()
            ->filter(function ($room) {
                return $room->reservations_count < $room->room_capacity;
            })
            ->groupBy('rental_id');
    
        return view("admin.reservation", compact('reservations', 'availableRooms'));
    }
    
    
    


    

    public function reservationHistory($reservation_id)
    {
        // Fetch the reservation record with related user and admin information
        $reservation = Reservation::with(['user', 'updatedBy'])->find($reservation_id);

        if (!$reservation) {
            return redirect()->route('admin.reservation')->with('error', 'Reservation not found.');
        }

        // Create a history array with admin and user data
        $history = [
            [
                'user_name' => $reservation->user->name,
                'user_email' => $reservation->user->email,
                'admin_email' => $reservation->updatedBy ? $reservation->updatedBy->email : 'N/A', // Admin email
                'payment_status' => $reservation->payment_status,
                'rent_status' => $reservation->rent_status,
                'updated_at' => $reservation->updated_at->format('Y-m-d H:i:s')
            ]
        ];

        return view('admin.reservation-history', compact('reservation', 'history'));
    }




    public function event_items($reservation_id)
    {
        $reservation = Reservation::with('rental', 'user', 'dormitoryRoom')->find($reservation_id);
        if (!$reservation) {
            return redirect()->route('admin.reservations')->with('error', 'Reservation not found.');
        }

        $reservedDates = [];
        if (in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II'])) {
            $reservedDates = Reservation::where('rental_id', $reservation->rental_id)
                ->where('rent_status', 'reserved')
                ->pluck('reservation_date')
                ->toArray();
        }


        return view('admin.reservation-events', compact('reservation', 'reservedDates'));
    }

    public function updateReservationStatus(Request $request, $reservation_id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,advance/deposit complete,1st month complete,2nd month complete,3rd month complete,4th month complete,5th month complete,6th month complete,full payment complete,canceled',
            'rent_status' => 'required|in:pending,reserved,completed,canceled',
        ]);

        $reservation = Reservation::with('dormitoryRoom', 'user', 'rental')->find($reservation_id);

        if (!$reservation) {
            return redirect()->route('admin.reservation')->with('error', 'Reservation not found.');
        }

        $rentalName = $reservation->rental->name;

        // Automatically mark as "completed" if the reservation_date equals the end_date
        if ($reservation->reservation_date && $reservation->end_date) {
            $reservationDate = \Carbon\Carbon::parse($reservation->reservation_date);
            $endDate = \Carbon\Carbon::parse($reservation->end_date);

            if ($reservationDate->isSameDay($endDate)) {
                $reservation->rent_status = 'completed';
                $reservation->save();
            }
        }

        if ($reservation->ih_start_date && $reservation->ih_end_date) {
            $ihStartDate = \Carbon\Carbon::parse($reservation->ih_start_date);
            $ihEndDate = \Carbon\Carbon::parse($reservation->ih_end_date);

            if ($ihStartDate->isSameDay($ihEndDate)) {
                $reservation->rent_status = 'completed';
                $reservation->save();
            }
        }
        if ($request->input('rent_status') === 'reserved') {
            // Check for existing reserved status on the same reservation_date
            $existingReservation = Reservation::where('reservation_date', $reservation->reservation_date)
                ->where('rent_status', 'reserved')
                ->where('rental_id', $reservation->rental_id) // Same rental_id if necessary
                ->first();

            if ($existingReservation) {
                // Prevent update if there's already a reserved reservation for the same date
                return redirect()->route('admin.reservation')
                    ->with('error', 'This date is already reserved for another reservation.');
            }

            if (in_array($rentalName, ['Male Dormitory', 'Female Dormitory', 'International House II'])) {
                $rooms = DormitoryRoom::where('rental_id', $reservation->rental_id)
                    ->orderBy('room_number')
                    ->get();

                $roomAssigned = false;

                foreach ($rooms as $room) {
                    $reservedCount = Reservation::where('dormitory_room_id', $room->id)
                        ->where('rent_status', 'reserved')
                        ->count();

                    if ($reservedCount < $room->room_capacity) {
                        $reservation->dormitory_room_id = $room->id;
                        $roomAssigned = true;
                        break;
                    }
                }

                if (!$roomAssigned) {
                    return redirect()->route('admin.reservation')
                        ->with('error', 'No available rooms with sufficient capacity.');
                }
            }
        } elseif ($reservation->rent_status === 'reserved' && $request->input('rent_status') !== 'reserved') {
            // Free up the room if the rent status is being changed from 'reserved' to something else
            $reservation->dormitory_room_id = null;
        }

        $historyEntry = [
            'user_name' => $reservation->user->name,
            'user_email' => $reservation->user->email,
            'admin_email' => Auth::user()->email,
            'payment_status' => $request->input('payment_status'),
            'rent_status' => $request->input('rent_status'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];

        $history = $reservation->history ? json_decode($reservation->history, true) : [];
        array_unshift($history, $historyEntry);

        $reservation->history = json_encode($history);
        $reservation->payment_status = $request->input('payment_status');
        $reservation->rent_status = $request->input('rent_status');
        $reservation->updated_by = Auth::id();

        $reservation->save();

        return redirect()->route('admin.reservation-events', ['reservation_id' => $reservation_id])
            ->with('success', 'Status updated successfully.');
    }


    // Rentals Reports

    public function rentalsReports(Request $request)
    {
        // Get the current date and year
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;
        $currentMonthId = $currentDate->month;

        // Validate the incoming request
        $validated = $request->validate([
            'year'  => 'nullable|integer|min:2000|max:' . $currentYear,
            'month' => 'nullable|integer|min:1|max:12',
            'week'  => 'nullable|integer|min:1|max:6',
        ]);

        // Get selected year, month, and week from request, default to current year/month/week
        $selectedYear  = $validated['year']  ?? $currentYear;
        $selectedMonthId = $validated['month'] ?? $currentMonthId;
        $selectedWeekId = $validated['week']  ?? $currentDate->weekOfMonth;

        // Fetch available months and the selected month
        $availableMonths = DB::table('month_names')->orderBy('id')->get();
        $selectedMonth   = $availableMonths->firstWhere('id', $selectedMonthId);

        if (!$selectedMonth) {
            $selectedMonth   = $availableMonths->first();
            $selectedMonthId = $selectedMonth->id;
        }

        // Calculate the start and end of the selected month
        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // Fetch the latest 10 reservations
        $reservations = DB::table('reservations')
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get();

        // Fetch dashboard data for the selected month and year
        $dashboardDatas = DB::select("
            SELECT 
                SUM(total_price) AS TotalPaymentAmount,
                SUM(IF(payment_status = 'pending', total_price, 0)) AS TotalPaymentPendingAmount,
                SUM(IF(payment_status = 'completed', total_price, 0)) AS TotalPaymentCompletedAmount,
                SUM(IF(payment_status = 'canceled', total_price, 0)) AS TotalPaymentCanceledAmount
            FROM reservations
            WHERE created_at BETWEEN ? AND ?
        ", [$startOfMonth, $endOfMonth]);

        // Get totals from dashboard data
        $dashboardData = $dashboardDatas[0] ?? null;

        // Initialize totals
        $TotalPaymentAmount = $dashboardData->TotalPaymentAmount ?? 0;
        $TotalPaymentPendingAmount = $dashboardData->TotalPaymentPendingAmount ?? 0;
        $TotalPaymentCompletedAmount = $dashboardData->TotalPaymentCompletedAmount ?? 0;
        $TotalPaymentCanceledAmount = $dashboardData->TotalPaymentCanceledAmount ?? 0;

        // Weekly data for the selected month
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(Carbon::MONDAY);
            $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            // Ensure the start and end of the week don't exceed the month boundaries
            if ($startOfWeek->lt($startOfMonth)) {
                $startOfWeek = $startOfMonth;
            }
            if ($endOfWeek->gt($endOfMonth)) {
                $endOfWeek = $endOfMonth;
            }

            // Add valid week ranges
            if ($startOfWeek->lte($endOfMonth)) {
                $weekRanges[$week] = [$startOfWeek, $endOfWeek];
            }
        }

        // Fetch totals for each week
        $PaymentAmountW = []; // Initialize Payment Amount for Weekly Data
        $paymentPendingAmounts = [];
        $paymentCompletedAmounts = [];
        $paymentCanceledAmounts = [];

        foreach ($weekRanges as $week => [$startOfSelectedWeek, $endOfSelectedWeek]) {
            // Fetch total amounts for the week
            $weeklyData = DB::select("
                SELECT 
                    SUM(total_price) AS TotalPaymentAmount,
                    SUM(IF(payment_status = 'pending', total_price, 0)) AS TotalPaymentPendingAmount,
                    SUM(IF(payment_status = 'completed', total_price, 0)) AS TotalPaymentCompletedAmount,
                    SUM(IF(payment_status = 'canceled', total_price, 0)) AS TotalPaymentCanceledAmount
                FROM reservations
                WHERE created_at BETWEEN ? AND ?
            ", [$startOfSelectedWeek, $endOfSelectedWeek])[0] ?? null;

            // Store results in arrays
            $PaymentAmountW[$week] = $weeklyData->TotalPaymentAmount ?? 0;
            $paymentPendingAmounts[$week] = $weeklyData->TotalPaymentPendingAmount ?? 0;
            $paymentCompletedAmounts[$week] = $weeklyData->TotalPaymentCompletedAmount ?? 0;
            $paymentCanceledAmounts[$week] = $weeklyData->TotalPaymentCanceledAmount ?? 0;
        }

        // Prepare Weekly Data for View
        $PaymentAmountW = implode(',', $PaymentAmountW);
        $PaymentPendingAmountW = implode(',', $paymentPendingAmounts);
        $PaymentCompletedAmountW = implode(',', $paymentCompletedAmounts);
        $PaymentCanceledAmountW = implode(',', $paymentCanceledAmounts);

        // Monthly data for the selected year
        $monthlyDatas = DB::select("
            SELECT M.id AS MonthNo, M.name AS MonthName,
                IFNULL(D.TotalPaymentAmount, 0) AS TotalPaymentAmount,
                IFNULL(D.TotalPaymentPendingAmount, 0) AS TotalPaymentPendingAmount,
                IFNULL(D.TotalPaymentCompletedAmount, 0) AS TotalPaymentCompletedAmount,
                IFNULL(D.TotalPaymentCanceledAmount, 0) AS TotalPaymentCanceledAmount 
            FROM month_names M
            LEFT JOIN (
                SELECT 
                    MONTH(created_at) AS MonthNo,
                    SUM(total_price) AS TotalPaymentAmount,
                    SUM(IF(payment_status='pending', total_price, 0)) AS TotalPaymentPendingAmount,
                    SUM(IF(payment_status='completed', total_price, 0)) AS TotalPaymentCompletedAmount,
                    SUM(IF(payment_status='canceled', total_price, 0)) AS TotalPaymentCanceledAmount
                FROM reservations 
                WHERE YEAR(created_at) = ? 
                GROUP BY MONTH(created_at)
            ) D ON D.MonthNo = M.id
            ORDER BY M.id
        ", [$selectedYear]);

        // Prepare Monthly Data for View
        $PaymentAmountM = implode(',', array_map(function ($item) {
            return $item->TotalPaymentAmount;
        }, $monthlyDatas));
        $PaymentPendingAmountM = implode(',', array_map(function ($item) {
            return $item->TotalPaymentPendingAmount;
        }, $monthlyDatas));
        $PaymentCompletedAmountM = implode(',', array_map(function ($item) {
            return $item->TotalPaymentCompletedAmount;
        }, $monthlyDatas));
        $PaymentCanceledAmountM = implode(',', array_map(function ($item) {
            return $item->TotalPaymentCanceledAmount;
        }, $monthlyDatas));

        // Daily data within the selected week
        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();
        $selectedWeek   = $availableWeeks->firstWhere('week_number', $selectedWeekId);

        if (!$selectedWeek) {
            $selectedWeek   = $availableWeeks->first();
            $selectedWeekId = $selectedWeek->week_number;
        }

        // Define the start and end of the selected week
        if (array_key_exists($selectedWeekId, $weekRanges)) {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[$selectedWeekId];
        } else {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[1] ?? [$startOfMonth, $endOfMonth]; // Default to week 1 or full month
        }

        // Query for daily data within the selected week, grouped by day
        $dailyDatasRaw = DB::select("
            SELECT 
                DAYNAME(created_at) AS DayName,
                SUM(IF(payment_status = 'pending', total_price, 0)) AS TotalPaymentPendingAmount,
                SUM(IF(payment_status = 'completed', total_price, 0)) AS TotalPaymentCompletedAmount,
                SUM(IF(payment_status = 'canceled', total_price, 0)) AS TotalPaymentCanceledAmount
            FROM reservations 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DAYNAME(created_at)
            ORDER BY FIELD(DAYNAME(created_at), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
        ", [$startOfSelectedWeek, $endOfSelectedWeek]);

        // Define the desired order of days
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Create a map of DayName to daily data
        $dailyDataMap = collect($dailyDatasRaw)->keyBy('DayName');

        // Reorder the daily data from Monday to Sunday, filling missing days with zeros
        $sortedDailyDatas = collect($daysOfWeek)->map(function ($day) use ($dailyDataMap) {
            if ($dailyDataMap->has($day)) {
                return $dailyDataMap->get($day);
            } else {
                return (object)[
                    'DayName' => $day,
                    'TotalPaymentPendingAmount' => 0,
                    'TotalPaymentCompletedAmount' => 0,
                    'TotalPaymentCanceledAmount' => 0,
                ];
            }
        });

        // Prepare Daily Data for View
        $AmountD = implode(',', array_map(function ($item) {
            return $item->TotalPaymentPendingAmount + $item->TotalPaymentCompletedAmount + $item->TotalPaymentCanceledAmount;
        }, $sortedDailyDatas->toArray()));
        $PaymentPendingAmountD = implode(',', array_map(function ($item) {
            return $item->TotalPaymentPendingAmount;
        }, $sortedDailyDatas->toArray()));
        $PaymentCompletedAmountD = implode(',', array_map(function ($item) {
            return $item->TotalPaymentCompletedAmount;
        }, $sortedDailyDatas->toArray()));
        $PaymentCanceledAmountD = implode(',', array_map(function ($item) {
            return $item->TotalPaymentCanceledAmount;
        }, $sortedDailyDatas->toArray()));

        // Calculate the range of years to show in the dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        // Return View with all data
        $pageTitle = 'Rentals Reports';
        return view('admin.rentals_reports', compact(
            'reservations',
            'TotalPaymentAmount',
            'TotalPaymentPendingAmount',
            'TotalPaymentCompletedAmount',
            'TotalPaymentCanceledAmount',
            'PaymentAmountW',
            'PaymentPendingAmountW',
            'PaymentCompletedAmountW',
            'PaymentCanceledAmountW',
            'selectedMonth',
            'selectedYear',
            'availableMonths',
            'yearRange',
            'PaymentAmountM',
            'PaymentPendingAmountM',
            'PaymentCompletedAmountM',
            'PaymentCanceledAmountM',
            'sortedDailyDatas',
            'PaymentPendingAmountD',
            'PaymentCompletedAmountD',
            'PaymentCanceledAmountD',
            'AmountD',
            'selectedWeekId',
            'availableWeeks',
            'pageTitle'
        ));
    }

    // Rental Reports PDF 
    public function downloadPdfRentals(Request $request)
    {
        // Validate incoming request data (already in place)
        $request->validate([
            'monthly_rentals_img' => 'nullable|string',
            'weekly_rentals_img'  => 'nullable|string',
            'daily_rentals_img'   => 'nullable|string',
            'total_payment'       => 'nullable|numeric',
            'pending_amount'      => 'nullable|numeric',
            'completed_amount'    => 'nullable|numeric',
            'canceled_amount'     => 'nullable|numeric',
        ]);

        // Retrieve the images from the request
        $monthlyImage = $request->input('monthly_rentals_img');
        $weeklyImage  = $request->input('weekly_rentals_img');
        $dailyImage   = $request->input('daily_rentals_img');

        // Retrieve other totals from the request
        $totalAmounts = [
            'total_payment'   => $request->input('total_payment'),
            'pending_amount'  => $request->input('pending_amount'),
            'completed_amount' => $request->input('completed_amount'),
            'canceled_amount' => $request->input('canceled_amount'),
        ];

        // Pass all data to the view
        $data = [
            'monthlyImage' => $monthlyImage, // Ensure this is included
            'weeklyImage'  => $weeklyImage,
            'dailyImage'   => $dailyImage,
            'totalAmounts' => $totalAmounts,
        ];

        // Generate PDF with fixed paper size (A4) and portrait orientation
        $pdf = PDF::loadView('admin.rentals_reports_name_pdf', $data)
            ->setPaper('A4', 'portrait');

        // Generate a unique filename
        $filename = 'rentals_reports_' . now()->format('Y_m_d_H_i_s') . '.pdf';

        // Return the PDF download
        return $pdf->download($filename);
    }




    public function rentalsReportsName(Request $request)
    {
        // Get the current date and year
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;

        // Validate the incoming request
        $validated = $request->validate([
            'year'  => 'nullable|integer|min:2000|max:' . $currentYear,
        ]);

        // Get selected year from request, default to current year
        $selectedYear  = $validated['year']  ?? $currentYear;

        // Define rental names and corresponding colors
        $rentalColors = [
            'Male Dormitory' => '#6f42c1', // Purple
            'Female Dormitory' => '#fd7e14', // Orange
            'International House II' => '#20c997', // Cyan
            'International Convention Center' => '#e83e8c', // Pink
            'Rolle Hall' => '#007bff', // Blue
            'Swimming Pool' => '#ffc107', // Yellow
        ];

        // Define rental names as objects for iteration
        $rentalNames = collect([
            'Male Dormitory',
            'Female Dormitory',
            'International House II',
            'International Convention Center',
            'Rolle Hall',
            'Swimming Pool',
        ])->map(function ($name) {
            return (object)['name' => $name];
        });

        // Initialize data structures
        $monthlyData = [];
        $weeklyData = [];
        $dailyData = [];
        $reservationsPerRental = [];

        // Prepare Monthly Data
        foreach ($rentalNames as $rental) {
            $monthlyReservations = DB::table('reservations')
                ->select(DB::raw('MONTH(reservations.created_at) as month'), DB::raw('COUNT(*) as count'))
                ->join('rentals', 'reservations.rental_id', '=', 'rentals.id')
                ->whereYear('reservations.created_at', $selectedYear)
                ->where('rentals.name', $rental->name)
                ->groupBy(DB::raw('MONTH(reservations.created_at)'))
                ->orderBy(DB::raw('MONTH(reservations.created_at)'))
                ->pluck('count', 'month')
                ->toArray();

            // Initialize all months to 0
            $monthlyData[$rental->name] = array_fill(1, 12, 0);

            // Fill with actual data
            foreach ($monthlyReservations as $month => $count) {
                $monthlyData[$rental->name][$month] = $count;
            }

            // Calculate total reservations per rental
            $reservationsPerRental[$rental->name] = DB::table('reservations')
                ->join('rentals', 'reservations.rental_id', '=', 'rentals.id')
                ->whereYear('reservations.created_at', $selectedYear)
                ->where('rentals.name', $rental->name)
                ->count();
        }

        // Prepare Weekly Data within Each Month (Weeks 1-6)
        // Initialize weeks 1-6 to 0 for each rental
        foreach ($rentalNames as $rental) {
            $weeklyData[$rental->name] = array_fill(1, 6, 0); // Weeks 1-6

            // Fetch all reservations for the rental in the selected year
            $reservationsForRental = DB::table('reservations')
                ->join('rentals', 'reservations.rental_id', '=', 'rentals.id')
                ->whereYear('reservations.created_at', $selectedYear)
                ->where('rentals.name', $rental->name)
                ->select('reservations.created_at')
                ->get();

            foreach ($reservationsForRental as $reservation) {
                $date = Carbon::parse($reservation->created_at);
                $dayOfMonth = $date->day;

                // Calculate week of month (1-6)
                $weekOfMonth = (int)ceil($dayOfMonth / 7);

                if ($weekOfMonth > 6) {
                    $weekOfMonth = 6; // Assign to week 6 if exceeding
                }

                $weeklyData[$rental->name][$weekOfMonth]++;
            }
        }

        // Prepare Daily Data
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($rentalNames as $rental) {
            $dailyReservations = DB::table('reservations')
                ->select(DB::raw('DAYNAME(reservations.created_at) as day'), DB::raw('COUNT(*) as count'))
                ->join('rentals', 'reservations.rental_id', '=', 'rentals.id')
                ->whereYear('reservations.created_at', $selectedYear)
                ->where('rentals.name', $rental->name)
                ->groupBy(DB::raw('DAYNAME(reservations.created_at)'))
                ->orderByRaw("FIELD(DAYNAME(reservations.created_at), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->pluck('count', 'day')
                ->toArray();

            // Initialize all days to 0
            $dailyData[$rental->name] = array_fill_keys($daysOfWeek, 0);

            // Fill with actual data
            foreach ($dailyReservations as $day => $count) {
                $dailyData[$rental->name][$day] = $count;
            }
        }

        // Calculate the range of years to show in the dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        // Pass data to the view
        return view('admin.rentals_reports-name', compact(
            'rentalNames',
            'monthlyData',
            'weeklyData',
            'dailyData',
            'reservationsPerRental',
            'yearRange',
            'selectedYear',
            'rentalColors'
        ));
    }

    public function downloadPdfRentalsName(Request $request)
    {
        // Define rental names
        $rentalNames = [
            'Male Dormitory',
            'Female Dormitory',
            'International House II',
            'International Convention Center',
            'Rolle Hall',
            'Swimming Pool',
        ];

        // Define rental colors
        $rentalColors = [
            'Male Dormitory' => '#6f42c1', // Purple
            'Female Dormitory' => '#fd7e14', // Orange
            'International House II' => '#20c997', // Cyan
            'International Convention Center' => '#e83e8c', // Pink
            'Rolle Hall' => '#007bff', // Blue
            'Swimming Pool' => '#ffc107', // Yellow
        ];

        // Validate the incoming request data
        $rules = [
            'monthly_reservations_img' => 'nullable|string',
            'weekly_reservations_img'  => 'nullable|string',
            'daily_reservations_img'   => 'nullable|string',
        ];

        // Add dynamic validation rules for each rental name
        foreach ($rentalNames as $name) {
            $snakeName = Str::snake($name);
            $rules['reservations_' . $snakeName] = 'nullable|integer';
        }

        $request->validate($rules);

        // Retrieve the images
        $monthlyImage = $request->input('monthly_reservations_img');
        $weeklyImage  = $request->input('weekly_reservations_img');
        $dailyImage   = $request->input('daily_reservations_img');

        // Retrieve reservations per rental
        $reservationsPerRental = [];
        foreach ($rentalNames as $name) {
            $snakeName = Str::snake($name);
            $reservationsPerRental[$name] = $request->input('reservations_' . $snakeName, 0);
        }

        // Pass data to the PDF view
        $data = [
            'monthlyImage' => $monthlyImage,
            'weeklyImage'  => $weeklyImage,
            'dailyImage'   => $dailyImage,
            'reservationsPerRental' => $reservationsPerRental,
            'rentalColors' => $rentalColors,
        ];

        // Generate PDF with fixed paper size (A4) and portrait orientation
        $pdf = PDF::loadView('admin.rentals_reports_name_pdf', $data)
            ->setPaper('A4', 'portrait');

        // Generate a unique filename
        $filename = 'rentals_reports_name_' . now()->format('Y_m_d_H_i_s') . '.pdf';

        // Return the PDF download
        return $pdf->download($filename);
    }











    // try code 

    public function showUserReports()
    {
        return view('admin.user-reports');
    }

    /**
     * Generate the user report based on the selected date range.
     */
    public function generateUserReports(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Retrieve input and parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Query the database for new users within the date range
        $newUsersCount = User::whereBetween('created_at', [$startDate, $endDate])->count();

        // Aggregate user registrations by day
        $userRegistrations = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Prepare data for ApexCharts
        $chartData = [
            'dates' => $userRegistrations->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })->toArray(),
            'counts' => $userRegistrations->pluck('count')->toArray(),
        ];

        // Optionally, retrieve user details
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->get();

        // Return the results to the view
        return view('admin.user-reports', compact('newUsersCount', 'newUsers', 'startDate', 'endDate', 'chartData'));
    }

    // Controller method to generate the sales report
    public function generateInputSales(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
    
        // Retrieve input and parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        // Query sales data grouped by day
        $salesData = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total_sales'),
            DB::raw('SUM(CASE WHEN status = "reserved" THEN total ELSE 0 END) as reserved_sales'),
            DB::raw('SUM(CASE WHEN status = "pickedup" THEN total ELSE 0 END) as pickedup_sales'),
            DB::raw('SUM(CASE WHEN status = "canceled" THEN total ELSE 0 END) as canceled_sales')
        )
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
    
        // Aggregate totals
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $reservedSalesTotal = Order::where('status', 'reserved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
        $pickedUpSalesTotal = Order::where('status', 'pickedup')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
        $canceledSalesTotal = Order::where('status', 'canceled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
    
        // Prepare data for the chart
        $chartData = [
            'dates' => $salesData->pluck('date')->toArray(),
            'total_sales' => $salesData->pluck('total_sales')->toArray(),
            'reserved_sales' => $salesData->pluck('reserved_sales')->toArray(),
            'pickedup_sales' => $salesData->pluck('pickedup_sales')->toArray(),
            'canceled_sales' => $salesData->pluck('canceled_sales')->toArray(),
            'total_orders' => $totalOrders,
            'reserved_sales_total' => $reservedSalesTotal,
            'pickedup_sales_total' => $pickedUpSalesTotal,
            'canceled_sales_total' => $canceledSalesTotal,
        ];
    
        // Pass data to the view
        return view('admin.input-sales', compact('chartData', 'startDate', 'endDate'));
    }

    public function generateInputRentalReports(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
    
        // Parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        // Query rental data grouped by payment status and day
        $rentalData = DB::table('reservations')
            ->select(
                DB::raw('DATE(reservation_date) as date'),
                DB::raw('SUM(CASE WHEN payment_status = "pending" THEN total_price ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN payment_status = "full payment complete" THEN total_price ELSE 0 END) as full_payment'),
                DB::raw('SUM(CASE WHEN payment_status = "canceled" THEN total_price ELSE 0 END) as canceled'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    
        // Aggregate totals
        $reportData = [
            'dates' => $rentalData->pluck('date')->toArray(),
            'pending' => $rentalData->pluck('pending')->toArray(),
            'full_payment' => $rentalData->pluck('full_payment')->toArray(),
            'canceled' => $rentalData->pluck('canceled')->toArray(),
            'pending_total' => $rentalData->sum('pending'),
            'full_payment_total' => $rentalData->sum('full_payment'),
            'canceled_total' => $rentalData->sum('canceled'),
            'total_sales' => $rentalData->sum('total'),
        ];
    
        // Return to view
        return view('admin.input-rentals-reports', compact('startDate', 'endDate', 'reportData'));
    }



    public function downloadInputSales(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
    
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    
        $chartImage = $request->input('chart_image'); // Retrieve the Base64 chart image
    
        $salesData = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total_sales'),
            DB::raw('SUM(CASE WHEN status = "reserved" THEN total ELSE 0 END) as reserved_sales'),
            DB::raw('SUM(CASE WHEN status = "pickedup" THEN total ELSE 0 END) as pickedup_sales'),
            DB::raw('SUM(CASE WHEN status = "canceled" THEN total ELSE 0 END) as canceled_sales')
        )
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
    
        $chartData = [
            'dates' => $salesData->pluck('date')->toArray(),
            'total_sales' => $salesData->pluck('total_sales')->toArray(),
            'reserved_sales' => $salesData->pluck('reserved_sales')->toArray(),
            'pickedup_sales' => $salesData->pluck('pickedup_sales')->toArray(),
            'canceled_sales' => $salesData->pluck('canceled_sales')->toArray(),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'reserved_sales_total' => Order::where('status', 'reserved')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
            'pickedup_sales_total' => Order::where('status', 'pickedup')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
            'canceled_sales_total' => Order::where('status', 'canceled')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
        ];
    
        // Generate the PDF
        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);
    
        $html = view('admin.pdf-input-sales', compact('chartData', 'startDate', 'endDate', 'chartImage'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
    
        return $pdf->stream('sales-report.pdf');
    }
    
    public function downloadInputUsers(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $chartImage = $request->input('chart_image'); // Retrieve the Base64 chart image

        // Query for user data
        $newUsersCount = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->get();

        // Generate the PDF
        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);

        $html = view('admin.pdf-input-users', compact('startDate', 'endDate', 'newUsersCount', 'newUsers', 'chartImage'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Return the generated PDF as a download
        return $pdf->stream('user-report.pdf');
    }

    public function downloadInputRentalsReports(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Retrieve the Base64 chart image
        $chartImage = $request->input('chart_image');

        // Query rental data grouped by payment status and day
        $rentalData = DB::table('reservations')
            ->select(
                DB::raw('DATE(reservation_date) as date'),
                DB::raw('SUM(CASE WHEN payment_status = "pending" THEN total_price ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN payment_status = "full payment complete" THEN total_price ELSE 0 END) as full_payment'),
                DB::raw('SUM(CASE WHEN payment_status = "canceled" THEN total_price ELSE 0 END) as canceled'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Aggregate totals
        $reportData = [
            'dates' => $rentalData->pluck('date')->toArray(),
            'pending' => $rentalData->pluck('pending')->toArray(),
            'full_payment' => $rentalData->pluck('full_payment')->toArray(),
            'canceled' => $rentalData->pluck('canceled')->toArray(),
            'pending_total' => $rentalData->sum('pending'),
            'full_payment_total' => $rentalData->sum('full_payment'),
            'canceled_total' => $rentalData->sum('canceled'),
            'total_sales' => $rentalData->sum('total'),
        ];

        // Generate the PDF
        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);

        $html = view('admin.pdf-input-rentals', compact('startDate', 'endDate', 'reportData', 'chartImage'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Return the generated PDF as a download
        return $pdf->stream('rental-sales-report.pdf');
    }



}
