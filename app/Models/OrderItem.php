<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;


    protected $fillable = [
        'product_id',
        'variant_id',
        'order_id',
        'price',
        'quantity',
        'options',
        'rstatus',
    ];

    protected $casts = [
        'options' => 'array',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductAttributeValue::class, 'variant_id');
    }
   
}
