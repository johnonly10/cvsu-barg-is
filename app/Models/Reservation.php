<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  
use Illuminate\Database\Eloquent\Relations\HasMany;  



class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rental_id',
        'dormitory_room_id',
        'updated_by',
        'history',
        'reservation_date',
        'canceled_date',
        'time_slot',
        'rent_status',
        'payment_status',
        'pool_quantity',
        'total_price',
        'internal_quantity',
        'external_quantity',
        'reservation_ih2_date',
         'usage_type',
        
    ];
    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function dormitoryRoom()
    {
        return $this->belongsTo(DormitoryRoom::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by'); 
    }
    


    
}
