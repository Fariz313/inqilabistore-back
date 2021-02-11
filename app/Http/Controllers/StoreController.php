<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class StoreController extends Controller
{

    public function index()
    {
        $data = Store::paginate(10);
        return response()->json([
            "status" => "success",
            "data"   => $data
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'store_name' => 'required|string|max:60',
			'kode_provinsi' => 'required|integer',
			'kode_kota' => 'required|integer',
			'kode_kecamatan' => 'required|integer',
			'kode_desa' => 'required|integer',
			'address' => 'required|string',
			'contact' => 'required|regex:/^[0-9]+$/',
		]);

		if($validator->fails()){
			return response()->json([
				'status'	=> 0,
				'message'	=> $validator->errors()->toJson()
            ],400   );
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
            $store                  = new Store();
            $store->user_id 	    = $user->id;
            $store->store_name 	    = $request->store_name;
            $store->kode_provinsi 	= $request->kode_provinsi;
            $store->kode_kota 	    = $request->kode_kota;
            $store->kode_kecamatan 	= $request->kode_kecamatan;
            $store->kode_desa 	    = $request->kode_desa;
            $store->address 	    = $request->address;
            $store->contact 	    = $request->contact;
            if($request->input('description')){
                $store->description 	= $request->description;   
            }
            $store->save();

            return response()->json([
                'status'	=> 'success',
                'message'	=> 'Store berhasil ditambahkan'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status'	=> 'failed',
                'message'	=> 'Store gagal teregistrasi',
                'message2'	=> $th
            ], 400);
        }
    }

    public function show($id)
    {
        try {
            $store = Store::with(array('user'=>function($query){
                $query->select('id','name','uname');
            }))->find($id);
            return response()->json([
                'status'	=> 'success',
                'store'     => $store
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status'	=> 'failed',
                'store'     => 'Store Is Not Found'
            ], 404);
        }
        
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }

    public function check()
    {
        try {
            if(! $user = JWTAuth::parseToken()->authenticate()){
                return response()->json([
                    'status'	=> 'failed',
                    'message'	=> 'User Doesnt Authorized'
                ], 401);
            }
            $store = Store::where('user_id',$user->id);
            if($store->count()<1){
                return response()->json([
                    'status'	=> 'Success',
                    'message'	=> 'User Doesnt Have a Store yet',
                ], 200);
            }
            return response()->json([
                'status'	=> 'failed',
                'message'	=> 'User Aleready Have a Store',
                'id'	=> $store->get()->first()->id,
            ], 403);
        } catch (\Throwable $th) {
            return response()->json([
                'status'	=> 'failed',
                'message'	=> 'User Doesnt Authorized',
            ], 401);
        }
    }
}
