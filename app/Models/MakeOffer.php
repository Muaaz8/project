<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MakeOffer extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'seller_id',
        'buyer_id',
        'offer_price',
        'status',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function seller(){
        return $this->belongsTo(User::class,'seller_id','id');
    }
    public function buyer(){
        return $this->belongsTo(User::class,'buyer_id','id');
    }
}
