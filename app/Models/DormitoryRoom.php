<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DormitoryRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'room_number',
        'room_capacity',
        'start_date',
        'end_date',
        'ih_start_date',
        'ih_end_date',
        'dorm_type'
    ];
    
    /**
     * Get the rental that owns the dormitory room.
     */
    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
    

    /**
     * Get the reservations for the dormitory room.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    public function updateRoomStatus()
    {
        $reservedCount = $this->reservations()
            ->where('rent_status', 'reserved')
            ->count();

        if ($reservedCount === 0) {
            $this->room_status = 'empty';
        } elseif ($reservedCount < $this->room_capacity) {
            $this->room_status = 'not full';
        } else {
            $this->room_status = 'full';
        }

        $this->save();
    }


}
