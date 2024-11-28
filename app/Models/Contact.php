<?php

namespace App\Models;

use App\Models\ContactReplies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Contact extends Model
{
    use HasFactory;

    public function replies()
    {
        return $this->hasMany(ContactReplies::class);
    }

}
