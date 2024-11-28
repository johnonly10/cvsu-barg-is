<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ProductAttribute;


class ShopController extends Controller
{
 public function index(Request $request)
    {
        $o_column = "";
        $o_order = "";
        $order = $request->query('order') ? $request->query('order') : -1;
        $f_categories = $request->query('categories');
        $sex = $request->query('sex', ''); // Add sex parameter with default empty string
        
        switch($order) {
            case 1:
                $o_column = 'created_at';
                $o_order = 'DESC';
                break;
            case 2:
                $o_column = 'created_at';
                $o_order = 'ASC';
                break;
            case 3:
                $o_column = 'price';
                $o_order = 'ASC';
                break;
            case 4:
                $o_column = 'price';
                $o_order = 'DESC';
                break;
            default:
                $o_column = 'id';
                $o_order = 'DESC';
                break;
        }
        
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name', 'ASC')->get();
        
        // Modify query to include gender filter
        $products = Product::where(function ($query) use ($f_categories) {
            $query->whereIn('category_id', explode(',', $f_categories))->orWhereRaw("'" . $f_categories . "'=''"); // include all if no category is selected
        })
        ->when($sex !== '', function ($query) use ($sex) {
            return $query->where('sex', $sex);
        })
        ->orderBy($o_column, $o_order)
        ->paginate(9);
        
        // Check if the request is an AJAX request
        if ($request->ajax()) {
            return view('partials.products-list', compact('products')); 
        }

        return view('shop', compact('products', 'order', 'categories', 'f_categories', 'sex'));
    }


    public function product_details($product_slug)
    {
        $product = Product::with(['attributeValues.productAttribute'])
            ->where('slug', $product_slug)
            ->firstOrFail();
     
        $groupedAttributes = [];
        $uniqueAttributes = [];
        foreach ($product->attributeValues as $value) {
            $attributeId = $value->product_attribute_id;
            $groupedAttributes[$attributeId][] = $value;
            if (!isset($uniqueAttributes[$attributeId])) {
                $uniqueAttributes[$attributeId] = $value->productAttribute;
            }
        }
        
        $rproducts = Product::where('slug', '<>', $product_slug)->take(8)->get();
        return view('details', compact('product', 'rproducts', 'groupedAttributes', 'uniqueAttributes'));
    }
}
