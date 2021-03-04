<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'order';
    public function order_detail()
    {
        return $this->hasMany(orderDetail::class,'invoice_id','invoice');
    }
    public function address()
    {
        return $this->hasOne(Address::class,'id','address_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

}
