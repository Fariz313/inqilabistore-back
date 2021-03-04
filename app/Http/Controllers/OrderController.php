<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Store;   
use App\Models\Cart;   
use App\Models\Order;   
use App\Models\OrderDetail;   
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
require dirname(__FILE__) . './../midtrans-php-master/Midtrans.php';

class OrderController extends Controller
{
    public function index()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "failed",
                "message"=> "Not Authenticated"
            ], 401);
        }
        $order = Order::Where('user_id',$user->id)->with('user','order_detail','store','address')->paginate(10);
        return response()->json([
            "status" => "sucsess",
            "order"   => $order
        ],200);
    }
    public function addOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'cart' => 'required|array',
			'address_id' => 'required',
        ]);  
        if($validator->fails()){
			return response()->json([
				'status'	=> 0,
				'message'	=> $validator->errors()->toJson()
            ],400);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "failed",
                "message"=> "Not Authenticated"
            ], 401);
        }
        try {
            $cart = Cart::where(function($query) use ($request){
                foreach ($request->cart as $key) {
                    $query->orWhere('id',$key);
                }
            })->with('book')->get();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "failed",
                "message"   => "Cart not valid"
            ],400);
        }
        try {
            $order = new Order();
            $order->invoice = date("Hymsdi-") . base_convert(rand(100,999), 10, 36);   
            $order->user_id = $user->id;
            $order->store_id = $cart[0]->store_id;
            $totalPrice = 0;
            foreach ($cart as $key) {
                $totalPrice += $key->count*($key->book->price - ($key->book->price * $key->book->discount / 100));
            }
            $order->total = $totalPrice;
            $order->status = 'pending';
            $order->payment_method = "-";
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "failed",
                "message"   => "Failed Create Order"
            ],400);
        }
        try {
            for ($i=0; $i < count($cart); $i++) { 
                 $order_detail[$i] = new OrderDetail();
                 $order_detail[$i]->book_id = $cart[$i]->book_id;
                 $order_detail[$i]->store_id = $cart[$i]->book->store_id;
                 $order_detail[$i]->user_id = $cart[$i]->user_id;
                 $order_detail[$i]->invoice_id = $order->invoice;
                 $order_detail[$i]->count = $cart[$i]->count;
                 $order_detail[$i]->note = $cart[$i]->note;
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "failed",
                "message"   => "Failed Create Order Detail"
            ],400);
        }
        try {
            $params = array(
                'transaction_details' => array(
                    'order_id' => $order->invoice,
                    'gross_amount' => $order->total,
                ),
                'customer_details' => array(
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                ),
            );
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "failed",
                "message"   => "Failed Create Order Detail"
            ],400);
        }
        // try {
        //     try{
                $snapToken = \Midtrans\Snap::getSnapToken($params);
                $order->payment_method =  $snapToken;
                $order->address_id =  $request->input("address_id");
                $order->save();
                foreach ($order_detail as $key ) {
                    $key->save();
                }
                return response()->json([
                    "status"    => "success",
                    "token"     => $snapToken,
                    "message"   => "Transaction Berhasil"
                ],200);
            // } catch (\Throwable $th) {
            //     $order->delete();
            //     foreach ($order_detail as $key ) {
            //         $key->delete();
            //     }
            //     return response()->json([
            //         "status" => "failed",
            //         "message"   => "Failed Create Snap Transaction"
            //     ],400);
            // }
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         "status" => "failed",
        //         "message"   => "Failed Create Snap Transaction"
        //     ],400);
        // }


    }

    public function midtrans()  
    {
        
        $params = array(
            'transaction_details' => array(
                'order_id' => "49sd",
                'gross_amount' => 10000,
            ),
            'customer_details' => array(
                'first_name' => 'budi',
                'last_name' => 'pratama',
                'email' => 'budi.pra@example.com',
                'phone' => '08111222333',
            ),
        );
        
    }
}