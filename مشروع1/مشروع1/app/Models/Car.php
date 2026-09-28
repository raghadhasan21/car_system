<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'year',
        'price',
        'mileage',
        'fuel_type',
        'transmission',
        'color',
        'description',
        'status',
        'image',
        'contact_phone',
        'user_id',
        'old_price',
        'discount_percent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function isFavoritedBy($userId = null)
    {
        if (!$userId && auth()->check()) {
            $userId = auth()->id();
        }
        
        if (!$userId) {
            return false;
        }
        
        return $this->favoritedBy()->where('user_id', $userId)->exists();
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function ratingsCount()
    {
        return $this->ratings()->count();
    }

    public function getDiscountedPriceAttribute()
    {
        if ($this->discount_percent > 0) {
            return $this->price - ($this->price * $this->discount_percent / 100);
        }
        return $this->price;
    }
}