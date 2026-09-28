<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $fillable = [
        'car_id', 'seller_id', 'start_price', 'current_price', 'end_time', 'status'
    ];

    public function car() { return $this->belongsTo(Car::class); }
    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function bids() { return $this->hasMany(Bid::class); }
}