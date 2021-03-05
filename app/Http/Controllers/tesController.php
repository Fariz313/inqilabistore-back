<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;   
use App\Models\OrderDetail;  

class tesController extends Controller
{
    public function tes()
    {
        $order = Order::find(1);
        $order->status = "success";
        $order->save();
    }
}
