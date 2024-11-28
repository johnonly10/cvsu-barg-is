<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_attribute_id',
        'value',
        'price',
        'quantity',
        'stock_status'
    ];



    public function getAttributesArray()
    {
        $productAttribute = $this->productAttribute;
        if ($productAttribute) {
            return [
                $productAttribute->name => $this->value
            ];
        }
        return ['Unknown' => $this->value];
    }
  
    
    public function productAttribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }
 

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
   

}
