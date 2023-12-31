<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // public function book()
    // {
    //     return $this->hasMany(User::class, 'seller_id');
    // }
}
