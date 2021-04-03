<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('tes', 'bookController@tes64');
Route::post('getongkir', 'CartController@getOngkir');
Route::prefix('/admin')->group(function () {
    Route::get('/', 'DashboardController@index');
    Route::get('/order', 'OrderController@allIndex');
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::post('login/admin', 'UserController@loginAdmin');
Route::get('login/check', 'UserController@getAuthenticatedUser');
Route::get('login/checkfull', 'UserController@getAuthenticatedUserFull');
Route::post('register/address', 'UserController@registerAddress');
Route::get('address', 'UserController@getAddress');
Route::post('user/edit', 'UserController@editUser');
Route::post('logout', 'UserController@logout');

Route::get('midtrans', 'OrderController@midtrans');

Route::prefix('/store')->group(function () {
    Route::get('/show/{id}','StoreController@show');
});

Route::prefix('/book')->group(function () {
    Route::get('/', 'BookController@home');
    Route::get('/s/book', 'BookController@index');
    Route::get('/{id}', 'BookController@show');
    Route::get('/store/{id}', 'BookController@getBookStore');
    Route::get('/g/genre', 'BookController@getGenre');
    Route::get('/a/genre', 'BookController@getAllGenre');
});


Route::middleware(['jwt.verify'])->group(function(){

    Route::post('/photo', 'UserController@uploadPhoto');
    Route::prefix('/book')->group(function () {
        Route::post('/', 'BookController@store');
        Route::post('/{id}', 'BookController@update');
    });
    Route::prefix('/store')->group(function () {
        Route::post('/','StoreController@store');
        Route::get('/','StoreController@index');
        Route::get('/ishavestore','StoreController@check');
    });
    Route::prefix('/cart')->group(function () {
        Route::get('/','CartController@index');
        Route::post('/{id}','CartController@store');
        Route::delete('/{id}','CartController@delete');
    });
    Route::prefix('/order')->group(function () {
        Route::get('/','OrderController@index');
        Route::post('/','OrderController@addOrder');
        Route::delete('/{id}','CartController@delete');
    });
    
    
});
