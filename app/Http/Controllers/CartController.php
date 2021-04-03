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
    public function getOngkir(Request $request)
    {
        
        try {
            $validator = Validator::make($request->all(), [
                'origin_prov'      => 'required|integer',
                'origin_kot'      => 'required|integer',
                'destination_prov' => 'required|integer',
                'destination_kot' => 'required|integer',
                'weight' => 'required|integer',
                'courier'     => 'required|string',
                ]);  
                if($validator->fails()){
                    return response()->json($validator->errors()->all   (), 400);
                }
                $OParams = "origin=".$this->getCode($request->origin_prov,$request->origin_kot)."&destination=".$this->getCode($request->destination_kot,$request->destination_prov)."&weight=".$request->weight."&courier=".$request->courier;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $OParams,
                CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: 1acfee7e906824a853e4587248c2b3aa"
            ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return response()->json([
                    "status" => "failed",
                    "message"=> "Error"
                ], 401);
            } else {
                return response()->json([
                    "status" => "success",
                    "ongkir" => json_decode($response,true) 
                ], 200);
            }
            return response()->json([
                "status" => "failed",
                "message"=> "Error"
            ], 401);
        } catch (\Throwable $th) {
            return $th;
        }
    }
    public function getCode($provinsi,$kota)
    {
        try {
            $curl = curl_init();
            $curlLw = curl_init();

            curl_setopt_array($curlLw, array(
                CURLOPT_URL => "https://raw.githubusercontent.com/Fariz313/public-repo/main/indonesia-region.min.json",
                CURLOPT_RETURNTRANSFER => true,
            )); 
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.rajaongkir.com/starter/city",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: 1acfee7e906824a853e4587248c2b3aa"
            ),
            ));
            $response = curl_exec($curl);
            $responseLw = curl_exec($curlLw);
            $err = curl_error($curl);
            $errLw = curl_error($curlLw);
            $json = json_decode($response, true); 
            $jsonLw = json_decode($responseLw,true); 
            curl_close($curl);
            curl_close($curlLw);
                
            if ($errLw) {
                return "errlw salah";
            }
            if ($err) {
                return "err salah";
            }
            else {
                $lparr = $jsonLw[2]['name'];
                $lwarr = explode(" ",$jsonLw[$provinsi]['regencies'][$kota]['name']);
                $lwarrType = $lwarr[0];
                unset($lwarr[0]);
                $lwstr = implode(" ",$lwarr);
                $jsonFilter = $json['rajaongkir']['results'];
                $jsonFiltered = collect($jsonFilter)->where("type",ucwords(strtolower($lwarrType)))->where("city_name",ucwords(strtolower($lwstr)))->first();
                if($jsonFiltered==null){
                    $blwarr = $lwarr[1];
                    unset($lwarr[1]);
                    $lwstr = implode($lwarr);
                    $jsonFilter = $json['rajaongkir']['results'];
                    $jsonFiltered = collect($jsonFilter)->where("type",ucwords(strtolower($lwarrType)))->where("city_name",ucwords(strtolower($blwarr." ".$lwstr)))->first();
                }
                return $jsonFiltered["city_id"];
            }
        } catch (\Throwable $th) {
            return "catch"; 
        };
        

    }
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
            $cart = Store::select('id','store_name','kode_kota','kode_provinsi')
                ->with(array('cart'=>function($query) use ($user){
                $query->select('cart.id','cart.store_id','cart.count','cart.note','book.name','book.price','book.stock','book.discount','book.weight','store.store_name')
                 ->where('cart.user_id',$user->id)
                ->orderBy('store_id') 
                ->join('book','cart.book_id','=','book.id')
                ->join('store','cart.store_id','=','store.id');
            }))
            ->whereHas('cart',function($q) use ($user){
                $q->where('user_id',$user->id);
            })
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
                $storecheck = Cart::where('book_id',$id)->where('user_id',$user->id)->first();    
                if($storecheck!==null){
                    $storecheck->count += $request->count;
                    if($storecheck->save()){
                        return response()->json([
                            "status" => "success",
                            "message"=> "Book Added to Cart"
                        ], 200);
                    }else{
                        return response()->json([
                            "status" => "failed",
                            "message"=> "Book ss"
                        ], 404);        
                    }
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
