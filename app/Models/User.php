<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $guarded = [];
    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function books()
    {
        return $this->hasMany(Book::class, 'user_id')->orderBy('id', 'desc');
    }
    public function buyOrder()
    {
        return $this->hasMany(Order::class, 'buyer_id')->orderBy('id', 'desc');
    }
    public function sellOrder()
    {
        return $this->hasMany(Order::class, 'seller_id')->orderBy('id', 'desc');
    }
}
