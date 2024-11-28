<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\PreOrder;
use App\Models\OrderItem;
use App\Models\CourseDept;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Events\LowStockEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Notifications\LowStockNotification;
use App\Notifications\PreOrderNotification;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }

    public function add_to_cart(Request $request)
    {
        if (!Auth::check()) {
            Log::info('User not authenticated');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $product = Product::find($request->id);

        if (!$product) {
            Log::error('Product not found with ID: ' . $request->id);
            return redirect()->back()->with('error', 'Product not found.');
        }

        // $userSex = Auth::user()->sex;

        // if ($product->sex !== 'all' && $product->sex !== $userSex) {
        //     Log::warning('Product sex mismatch. Product ID: ' . $product->id . ' - User sex: ' . $userSex);
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You cannot add this product to the cart due to sex restrictions.'
        //     ], 403);
        // }
        

        // Check if the product has variants
        $hasVariants = $product->attributeValues()->exists();
        $variantAttributes = null;

        Log::info('Product ID ' . $product->id . ' - Checking for variants: ' . ($hasVariants ? 'Has Variants' : 'No Variants'));

        if ($hasVariants && (!$request->has('variant_id') || empty($request->variant_id))) {
            Log::warning('Variant not selected for product with variants. Product ID: ' . $product->id);
            return redirect()->back()->withErrors(['variant_id' => 'Please select a product variant.'])->withInput();
        }

        
        if ($hasVariants) {
            $variant = $product->attributeValues->where('id', $request->variant_id)->first();

            if (!$variant) {
                return redirect()->back()->with('error', 'Invalid variant selected.');
            }

            if ($variant->quantity <= 0 || $variant->stock_status === 'outofstock') {
                return redirect()->back()->with('error', 'This variant is out of stock.');
            }

            if ($request->quantity > $variant->quantity) {
                return redirect()->back()->with('error', 'Cannot add more than available stock for this variant.');
            }


            $variantAttributes = $variant->getAttributesArray();
            $attributeValues = array_values($variantAttributes);
            $variantNameSuffix = ' - ' . implode(', ', $attributeValues);

                 // Log variant details
        Log::info('Adding variant to cart', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'variant_attributes' => $variantAttributes,
            'price' => $variant->price
        ]);

            // Add variant to the cart
            Cart::instance('cart')->add([
                'id' => $product->id,
                'name' => $product->name . $variantNameSuffix,
                'qty' => $request->quantity,
                'price' => $variant->price,
                'options' => [
                    'product_id' => $product->id,
                    'is_variant' => true,
                    'variant_id' => $variant->id,
                    'variant_quantity' => $variant->quantity,
                    'variant_attributes' => $variantAttributes
                ]
            ])->associate('App\Models\Product');
        } else {
            if ($request->quantity > $product->quantity) {
                return redirect()->back()->with('error', 'Cannot add more than available stock.');
            }

            Log::info('Adding product without variant to cart', [
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price
            ]);

            Cart::instance('cart')->add([
                'id' => $product->id,
                'name' => $product->name,
                'qty' => $request->quantity,
                'price' => $product->price,
                'options' => [
                    'product_id' => $product->id,
                    'is_variant' => false,
                    'variant_attributes' => null
                ]
            ])->associate('App\Models\Product');
        }
        Log::info('Product added to cart successfully for Product ID: ' . $product->id);
        return response()->json(['incomplete_profile' => true, 'message' => 'Your password has been successfully updated. Please complete your profile to proceed.'], 200);
    }




    public function increase_cart_quantity($rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        if (!$cartItem) {
            return response()->json(['error' => 'The item does not exist in the cart.']);
        }

        $newQty = $cartItem->qty + 1;
        $maxQty = $cartItem->options['is_variant'] ? $cartItem->options['variant_quantity'] : $cartItem->model->quantity;

        if ($newQty > $maxQty) {
            return response()->json(['error' => 'Cannot increase quantity. Stock is limited.']);
        }

        $product = Product::find($cartItem->options['product_id']);

        if (isset($cartItem->options['variant_id'])) {
            $variant = ProductAttributeValue::find($cartItem->options['variant_id']);
            if ($variant && $cartItem->qty < $variant->quantity) {
                $newQty = $cartItem->qty + 1;
                Cart::instance('cart')->update($rowId, $newQty);
            } else {
                return response()->json(['error' => 'Cannot increase quantity. Variant stock is limited.']);
            }
        } else {
            if ($cartItem->qty < $product->quantity) {
                $newQty = $cartItem->qty + 1;
                Cart::instance('cart')->update($rowId, $newQty);
            } else {
                return response()->json(['error' => 'Cannot increase quantity. Product stock is limited.']);
            }
        }

        return response()->json([
            'success' => true,
            'newQty' => $newQty,
            'subtotal' => Cart::subtotal(),
            'total' => Cart::total(),
            'itemTotal' => number_format($cartItem->price * $newQty, 2)
        ]);
    }

    public function decrease_cart_quantity($rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        if (!$cartItem) {
            return response()->json(['error' => 'The item does not exist in the cart.']);
        }

        $newQty = $cartItem->qty - 1;

    if ($newQty < 1) {
            return response()->json(['error' => 'Quantity cannot be less than 1.']);
        }

        Cart::instance('cart')->update($rowId, $newQty);

        return response()->json([
            'success' => true,
            'newQty' => $newQty,
            'subtotal' => Cart::subtotal(),
            'total' => Cart::total(),
            'itemTotal' => number_format($cartItem->price * $newQty, 2)
        ]);
    }


    public function updateVariant(Request $request, $rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        if (!$cartItem) {
            return redirect()->back()->with('error', 'The cart does not contain the specified item.');
        }
    
        $product = Product::with('attributeValues.productAttribute')->find($cartItem->options['product_id']);
    
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }
    
        $variantAttributes = [];
        $variantIds = [];
        foreach ($request->input('attribute', []) as $attributeId => $variantId) {
            $attributeValue = $product->attributeValues->where('id', $variantId)->first();
            if ($attributeValue && $attributeValue->productAttribute) {
                $variantAttributes[$attributeValue->productAttribute->name] = $attributeValue->value;
                $variantIds[] = $variantId;
            }
        }
    
     
        $matchingVariant = $product->attributeValues()->whereIn('id', $variantIds)->first();
    
        if ($matchingVariant) {
            // Update the cart item with new variant details
            Cart::instance('cart')->update($rowId, [
                'id' => $product->id, // Use only product_id
                'name' => $product->name . ' - ' . implode(', ', $variantAttributes),
                'price' => $matchingVariant->price,
                'options' => array_merge($cartItem->options->toArray(), [
                    'variant_id' => $matchingVariant->id,
                    'variant_attributes' => $variantAttributes,
                    'is_variant' => true,
                    'variant_quantity' => $matchingVariant->quantity,
                ]),
                
            ]);
            return redirect()->back()->with('success', 'Variant updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Selected variant is not available.');
        }
    }
    


    public function remove_item($rowId)
    {
        // Check if the cart contains the rowId
        if (!Cart::instance('cart')->get($rowId)) {
            return redirect()->back()->with('error', 'The item does not exist in the cart.');
        }
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }


    public function empty_cart()
    {
        Cart::instance('cart')->destroy();
        return redirect()->back();
    }


    public function updateQuantity($action, $rowId)
    {
        try {
            $item = Cart::get($rowId);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found'
                ]);
            }

            $currentQty = $item->qty;
            $newQty = $action === 'increase' ? $currentQty + 1 : $currentQty - 1;

            if ($newQty < 1) {
                Cart::remove($rowId);
            } else {
                Cart::update($rowId, $newQty);
            }

            // Force Cart recalculation
            Cart::setGlobalTax(Cart::getGlobalTax());

            return response()->json([
                'success' => true,
                'newQty' => $newQty,
                'subtotal' => Cart::subtotal(),
                'total' => Cart::total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $availableDatesAndTimeSlots = $this->getAvailableDatesAndTimeSlots();

        return view('checkout', compact('user', 'availableDatesAndTimeSlots'));
    }




    public function place_an_order(Request $request)
    {
        $user = Auth::user();

        // Check if profile information is complete based on role
        if ($this->isProfileIncomplete($user)) {
            return redirect()->route('user.profile')->with([
                'incomplete_profile' => true,
                'message' => 'Please complete your profile to proceed with the checkout.'
            ]);
        }

        // Proceed with the order placement if profile is complete
        $this->setTotalAmount();

        try {
            // Create a new Order
            $order = new Order();
            $order->user_id = $user->id;
            $order->subtotal = Session::get('checkout')['subtotal'];
            $order->total = Session::get('checkout')['total'];
            $order->name = $user->name;
            $order->phone_number = $user->phone_number;
            $order->year_level = $user->year_level;
            $order->department = $user->department;
            $order->course = $user->course;
            $order->email = $user->email;
            $order->reservation_date = $request->input('reservation_date');
            $order->time_slot = $request->input('time_slot');
            $order->save();


            foreach (Cart::instance('cart')->content() as $item) {
                $orderItem = new OrderItem();
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->price = $item->price;
                $orderItem->quantity = $item->qty;

                // Handle variants (variant_id)
                if ($item->options->has('is_variant') && $item->options->has('variant_id')) {
                    // Reduce stock of the variant
                    $variant = ProductAttributeValue::find($item->options->variant_id);
                    if (!$variant || $variant->quantity < $item->qty) {
                        throw new \Exception('Insufficient variant stock for product: ' . $item->name);
                    }

                    $variant->quantity -= $item->qty;
                    $variant->stock_status = $variant->quantity <= 0 ? 'outofstock' : 'instock';
                    $variant->save();
                    // Trigger low stock event if needed
                    if ($variant->quantity <= 20) {
                        $product = $variant->product; // Access the related product
                        $quantity = $variant->quantity; // Use the variant quantity
                        \Log::info('LowStockEvent triggered for variant', ['product' => $product, 'quantity' => $quantity]);
                        $admin = User::where('utype', 'ADM')->first();
                        if ($admin) {
                            $admin->notify(new LowStockNotification($product, $quantity));
                        }
                        event(new LowStockEvent($product, $quantity));
                    }
                } else {
                   
                    $product = Product::find($item->id);
                    if (!$product || $product->quantity < $item->qty) {
                        throw new \Exception('Insufficient stock for product: ' . $item->name);
                    }

                    $product->quantity -= $item->qty;
                    $product->stock_status = $product->quantity <= 0 ? 'outofstock' : 'instock';
                    $product->save();
                    
                    if ($product->quantity <= 20) {
                        $quantity = $product->quantity;
                        \Log::info('LowStockNotification triggered for product', ['product' => $product, 'quantity' => $product->quantity]);
                        $admin = User::where('utype', 'ADM')->first();
                        if ($admin) {
                            $admin->notify(new LowStockNotification($product, $quantity));
                        }
                        event(new LowStockEvent($product, $product->quantity));
                    }
                }

                $orderItem->options = json_encode($item->options->toArray());
                $orderItem->save();
            }

            // Create a new Transaction
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->order_id = $order->id;
            $transaction->status = "pending";
            $transaction->save();


            Cart::instance('cart')->destroy();
            Session::forget('checkout');
            Session::put('order_id', $order->id);

            return redirect()->route('cart.order.confirmation');
        } catch (\Exception $e) {

            \Log::error('Order placement failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to place order. ' . $e->getMessage());
        }
    }
    private function isProfileIncomplete($user)
    {
        if ($user->role === 'student') {
            return !$user->name || !$user->email || !$user->phone_number || !$user->year_level || !$user->department || !$user->course;
        } elseif ($user->role === 'professor') {
            return !$user->name || !$user->email || !$user->phone_number || !$user->department;
        } else {
            // For other roles, only name, email, and phone number are required
            return !$user->name || !$user->email || !$user->phone_number;
        }
    }




    public function setTotalAmount()
    {
        if (Cart::instance('cart')->content()->count() > 0) {
            $subtotal = (float) str_replace(',', '', Cart::instance('cart')->subtotal());
            $total = (float) str_replace(',', '', Cart::instance('cart')->total());

            Session::put('checkout', [
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
            ]);
        } else {
            Session::forget('checkout');
        }
    }

    public function order_confirmation()
    {
        if (Session::has('order_id')) {
            $order = Order::find(Session::get('order_id'));
            return view('order-confirmation', compact('order'));
        }
        return redirect()->route('cart.index');
    }

    public function getUnavailableDates()
    {
        $apiKey = 'SPNrrilajlxqFgYvY5uYnftQ5NREo7YS';
        $country = 'PH';
        $year = Carbon::now()->year;
        $url = "https://calendarific.com/api/v2/holidays?api_key=" . urlencode($apiKey) . "&country=" . urlencode($country) . "&year=" . urlencode($year);
        $response = Http::get($url);
        $unavailableDates = [];

        if ($response->successful()) {
            $holidays = $response->json()['response']['holidays'];
            foreach ($holidays as $holiday) {
                $unavailableDates[] = Carbon::parse($holiday['date']['iso'])->toDateString();
            }
        }

        return $unavailableDates;
    }

    public function getAvailableDatesAndTimeSlots()
    {
        $dates = [];
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addMonths(3);
        $unavailableDates = $this->getUnavailableDates();
        $timeSlotsConfig = [
            '08:00 am - 09:00 am' => 50,
            '10:00 am - 11:00 am' => 50,
            '01:00 pm - 02:00 pm' => 50,
            '03:00 pm - 04:00 pm' => 50,
        ];

        while ($startDate->lte($endDate)) {
            if (in_array($startDate->toDateString(), $unavailableDates)) {
                $startDate->addDay();
                continue;
            }
            $timeSlots = [];
            foreach ($timeSlotsConfig as $startTime => $limit) {
                $slotsLeft = $limit - $this->countReservations($startDate->toDateString(), $startTime);
                if ($slotsLeft > 0) {
                    $timeSlots[] = [
                        'time_slot' => $startTime,
                        'slots_left' => $slotsLeft,
                    ];
                }
            }
            if (!empty($timeSlots)) {
                $dates[] = [
                    'date' => $startDate->toDateString(),
                    'timeSlots' => $timeSlots,
                ];
            }
            $startDate->addDay();
        }

        return response()->json($dates);
    }

    private function countReservations($date, $timeSlot)
    {
        return Order::where('reservation_date', $date)
            ->where('time_slot', $timeSlot)
            ->count();
    }
    private function isTimeSlotAvailable($date, $timeSlot, $limit)
    {
        $reservations = $this->countReservations($date, $timeSlot);
        return $reservations < $limit;
    }
}
