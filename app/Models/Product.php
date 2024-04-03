<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'product';
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'category_id',
        'sub_category_id',
        'condition',
        'make_and_model',
        'mileage',
        'color',
        'fix_price',
        'firm_on_price',
        'auction_price',
        'starting_date',
        'starting_time',
        'ending_date',
        'ending_time',
        'sell_to_us',
        'location',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function category(){
        return $this->belongsTo(Categories::class);
    }

    public function sub_category(){
        return $this->belongsTo(Sub_Category::class);
    }

    public function photo(){
        return $this->hasMany(Photo::class);
    }
}
