<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;   
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $order = Order::count();
            $user = User::count();
            return response()->json([
                'status'	    => 'success',
                'message'	    => 'pick data success',
                'ordeCount'   => $order,
                'userCount'   => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'	=> 'failed',
                'message'	=> 'pick data failed'
            ], 400);
        }
    }
}
