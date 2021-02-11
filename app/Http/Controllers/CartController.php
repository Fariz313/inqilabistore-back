<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;   
use App\Models\Store;   
use App\Models\Cart;   
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use DB;

class CartController extends Controller
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
        try {
            // $cart = Cart::select('cart.id','cart.store_id','cart.count','cart.note','book.name','book.price','book.stock','store.store_name')
            // ->where('cart.user_id',$user->id)
            // ->orderBy('store_id') 
            // ->join('book','cart.book_id','=','book.id')
            // ->join('store','cart.store_id','=','store.id')
            // ->paginate(10);
            $cart = Store::select('id','store_name','kode_kota')
                ->with(array('cart'=>function($query) use ($user){
                $query->select('cart.id','cart.store_id','cart.count','cart.note','book.name','book.price','book.stock','book.discount','store.store_name')
                ->where('cart.user_id',$user->id)
                ->orderBy('store_id') 
                ->join('book','cart.book_id','=','book.id')
                ->join('store','cart.store_id','=','store.id');
            }))
            ->whereHas('cart')
            ->paginate(10);
            return response()->json([
                "status"=>"success",
                "data"=> $cart
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "status"=>"failed",
                "message"=>"Failed Get Data"
            ],500);
        }
    }
    public function store(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'note' => 'string',
                'count' => 'required|integer',
            ]);  
            if($validator->fails()){
                return response()->json($validator->errors()->all   (), 400);
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
                $bookchek   = Book::findorfail($id);
                $storecheck = Cart::where('book_id',$id)->first();    
                if($storecheck!==null){
                    $storecheck->count += $request->count;
                    $storecheck->save();
                    return response()->json([
                        "status" => "success",
                        "message"=> "Book Added to Cart"
                    ], 200);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    "status" => "failed",
                    "message"=> "Book Not Found"
                ], 404);
            }
            $cart           = new cart();
            $cart->count    = $request->count;
            if($request->input['note']){
                $cart->note    = $request->note;
            }
            $cart->user_id  = $user->id ;
            $cart->store_id = $bookchek->store_id ;
            $cart->book_id  = $id ;
            $cart->save();
            return response()->json([
                "status"=>"success",
                "message"=>"Success Inputed to Cart"
            ],200);
            
        } catch (\Throwable $th) {
            return response()->json([
                "status"=>"failed",
                "message"=>"Failed Inputed to Cart"
            ],500);
        }

    }
    public function delete($id)
    {
        try{

            Cart::findOrFail($id)->delete();

            return response([
            	"status"	=> 1,
                "message"   => "Cart deleteed."
            ]);
        } catch(\Exception $e){
            return response([
            	"status"	=> 0,
                "message"   => $e->getMessage()
            ]);
        }
    }
}
