<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'file',
        'file_name',
        'file_type',
        'status',
        'conversation_id',
        'offer_id',
        'product_id',
        'created_at',
        'updated_at',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function offer(){
        return $this->belongsTo(MakeOffer::class);
    }
}
