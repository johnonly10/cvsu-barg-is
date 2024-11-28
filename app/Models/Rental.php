<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rentals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'qualification',  
        'capacity',
        'status',
        'featured',
        'image',
        'images',
        'rules_and_regulations',  
        'requirements',
        'price'  ,
        'external_price',
        'internal_price',
        'exclusive_price'
    ];

    public function getTotalRoomCapacityAttribute()
    {
        return $this->dormitoryRooms->sum('room_capacity');
    }


    /**
     * Accessor for `featured` attribute.
     * Returns 'Yes' if featured, otherwise 'No'.
     */
    public function getIsFeaturedAttribute()
    {
        return $this->featured ? 'Yes' : 'No';
    }

    /**
     * Mutator for the `slug` attribute.
     * Automatically generates a slug when a name is set.
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = \Str::slug($value); 
    }

    public function dormitoryRooms()
    {
        return $this->hasMany(DormitoryRoom::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    

    /**
     * Accessor for image path.
     * This ensures a full URL is returned for images.
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Accessor for qualification file path.
     * Returns the full URL for the qualification file.
     */
    public function getQualificationUrlAttribute()
    {
        return $this->qualification ? asset('storage/' . $this->qualification) : null;
    }

    /**
     * Accessor for rules and regulations file path.
     * Returns the full URL for the rules and regulations file.
     */
    // public function getRulesAndRegulationsUrlAttribute()
    // {
    //     return $this->rules_and_regulations ? asset('storage/' . $this->rules_and_regulations) : null;
    // }

    /**
     * Accessor for requirements file path.
     * Returns the full URL for the requirements file.
     */
    public function getRequirementsUrlAttribute()
    {
        return $this->requirements ? asset('storage/' . $this->requirements) : null;
    }

    // class Rental extends Model
    // {
    //     protected $fillable = ['name']; // Update fields based on your table structure
    // }
    //yoko na sumabay putaina kaumay
}
