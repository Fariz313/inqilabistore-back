<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function loginAdmin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $user = JWTAuth::user();
        $admin = Admin::Where('user_id',$user->id)->get();
        if(count($admin)<1){
            return response()->json([
                'token' => "Failed",
                'logged'=> false,
                'user'=> "User Not Admin",
            ],202);
            if(JWTAuth::invalidate(JWTAuth::getToken())) {
                return response()->json([
                    "logged"    => false,
                    "message"   => 'Logout berhasil'
                ], 201);
            } else {
                return response()->json([
                    "logged"    => true,
                    "message"   => 'Logout gagal'
                ], 201);
            }
        }
        return response()->json([
            'token' => $token,
            'logged'=> true,
            'user'=> $user,
        ],202);
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $user = JWTAuth::user();
        return response()->json([
            'token' => $token,
            'logged'=> true,
            'user'=> $user,
        ],202);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'uname' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'birthdate' => 'required|date',
            'password' => 'required|string|min:6|confirmed',
            'gender' => 'required|in:m,f',
            'phone_number' => 'required',
            'photo' => 'file',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->all   (), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'uname' => $request->get('uname'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'birthdate' => $request->get('birthdate'),
            'gender' => $request->get('gender'),
            'phone_number' => $request->get('phone_number'),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function uploadPhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'photo' => 'required|file',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
            // return response()->json([
            //     'status'    =>'failed validate',
            //     'error'     =>'No Photo Uploaded'
            // ],400);
        }
        // try {
            $user  = JWTAuth::parseToken()->authenticate();
            $photo = $request->file('photo');
            $tujuan_upload = 'image/user';
            $photo_name = $user->id.'_'.date("h-i-sa").'_'.date("Y-m-d").'_'.Str::random(3).'.'.$photo->getClientOriginalExtension();
            $photo->move($tujuan_upload,$photo_name);
            $user->photo = $photo_name;
            $user->save();
            return response()->json([
                'status'    =>'success',
                'message'   =>'Yours Photo Uploaded'
            ],201);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'status'    =>'failed validate',
        //         'error'     =>'No Photo Uploaded'
        //     ],400);
        // }
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
            
            return response()->json([
                'auth' => 'true',
                'status' => '1',
                'user' => $user
            ],200);

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        } catch (\Throwable $th) {
            return response()->json([
                'auth' => 'false',
                'status' => '0',
                'message' => 'no token'
            ],404);;
        }

    }
    
    public function getAuthenticatedUserFull()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }
        $userData = User::with('address')->find($user->id);
                return response()->json([
                    'auth' => 'true',
                    'status' => '1',
                    'user' => $userData
                ],200);
    }
    
    
    public function registerAddress(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "failed",
                "message"=> "Not Authenticated"
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'kode_provinsi' => 'required|integer',
            'kode_kota' => 'required|integer',
            'kode_kecamatan' => 'required|integer',
            'kode_desa' => 'required|integer',
            'kode_pos' => 'required|integer',
            'alamat' => 'required|min:10',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // $address = Address::create([
        //     'kode_provinsi' => $request->get('kode_provinsi'),
        //     'kode_kota' => $request->get('kode_kota'),
        //     'kode_kecamatan' => $request->get('kode_kecamatan'),
        //     'kode_desa' => $request->get('kode_desa'),
        //     'kode_pos' => $request->get('kode_pos'),
        //     'alamat' => $request->get('alamat'),
        //     'catatan' => $request->get('catatan'),
        // ]);
            $data                       = new Address();
            $data->user_id              = $user->id;
            $data->kode_provinsi        = $request->input('kode_provinsi');
            $data->kode_kota            = $request->input('kode_kota');
            $data->kode_kecamatan       = $request->input('kode_kecamatan');
            $data->kode_desa            = $request->input('kode_desa');
            $data->kode_pos             = $request->input('kode_pos');
            $data->alamat               = $request->input('alamat');
            $data->catatan              = $request->input('catatan');
	        $data->save();

        return response()->json([
            "status" => "success",
            "message"=> "Address Aleready Added"
        ],201);
    }

    public function logout(Request $request)
    {

        if(JWTAuth::invalidate(JWTAuth::getToken())) {
            return response()->json([
                "logged"    => false,
                "message"   => 'Logout berhasil'
            ], 201);
        } else {
            return response()->json([
                "logged"    => true,
                "message"   => 'Logout gagal'
            ], 201);
        }

    }
    public function getAddress()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $data = Address::where('user_id',$user->id);
        return response()->json([
            "status" => "success",
            "data"   => $data
        ]);

    }
    public function editUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'uname' => 'string|max:255|unique:users',
            'email' => 'string|email|max:255|unique:users',
            'password' => 'string|min:6|confirmed',
            'gender' => 'in:m,f',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->all   (), 400);
        }

        
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }
        $userDb = User::findOrFail($user->id);
        if($request->get('name')){
            $userDb->name = $request->get('name');
        }if($request->get('uname')){
            $userDb->uname = $request->get('uname');
        }if($request->get('birthdate')){
            $userDb->birthdate = $request->get('birthdate');
        }if($request->get('phone_number')){
            $userDb->phone_number = $request->get('phone_number');
        }if($request->get('gender')){
            $userDb->gender = $request->get('gender');
        }
        $userDb->save();
        return response()->json([
            "status"    => "success",
            "message"   => "Profile berhasil di edit"
        ]);
    }
    public function GetVerifCode(){
        
    }
}