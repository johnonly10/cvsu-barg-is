<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'quantity',
        'stock_status',
        'featured',
        'image',
        'images',
        'sex',
        'category_id',
        'archived',
        'archived_at',
        'product_attribute_id',
        'low_stock_notified',
        'instock_quantity',
        'reorder_quantity',
        'outofstock_quantity',
    
    ];  

    protected $casts = [
        'images' => 'array',
    ];
    protected $dates = ['archived_at'];

    public function scopeArchived($query)
    {
        return $query->where('archived', 1);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('archived', 0);
    }


    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')->withPivot('quantity', 'price');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_attribute_values', 'product_id', 'product_attribute_id')
                    ->withPivot('value', 'price', 'quantity', 'stock_status');
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $appends = ['current_stock'];


    public function getCurrentStockAttribute()
    {
        if ($this->attributeValues->isNotEmpty()) {
            return $this->attributeValues->sum('quantity');
        }

        return $this->quantity;
    }

    public function checkLowStock()
    {
        $lowStockThreshold = 20;

        if ($this->current_stock < $lowStockThreshold && !$this->low_stock_notified) {
            $this->low_stock_notified = true;
            $this->save();

            $this->notifyLowStock();
        }
    }

    public function scopeFilterBySex($query, string $sex)
    {
        if ($sex === 'male') {
            return $query->where(function ($q) {
                $q->where('sex', 'male')
                  ->orWhere('sex', 'all');
            });
        } elseif ($sex === 'female') {
            return $query->where(function ($q) {
                $q->where('sex', 'female')
                  ->orWhere('sex', 'all');
            });
        }

        return $query; 
    }

    
    // public function notifyLowStock()
    // {
    //     $admins = User::where('utype', 'ADM')->get();
    //     if ($admins->isNotEmpty()) {
    //         Notification::send($admins, new LowStockNotification($this));
    //     }
    // }
}
