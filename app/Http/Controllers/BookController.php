<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;   
use App\Models\Genre;   
use App\Models\GenreBook;   
use App\Models\Store;   
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class BookController extends Controller
{

    public function tes()
    {
       return GenreBook::find(2)->genre;
    }

    public function index(Request $request)
    {
        try {
            // if($request->get('s')){
            //     $srch = $request->s;
            //     $data = Book::where(function ($query) use ($srch){
            //         $query->where('name','like',"%".$srch."%");
            //     })->with('genreBook.genre')->paginate(30);
            //     return response()->json([
            //         "status" => "success",
            //         "data"   => $data
            //     ]);
            // }
            $data = Book::with('genreBook.genre')->
            whereHas('genreBook', function ($query) use ($request) {
                if($request->get('g')){
                    return $query->where('genre_id', '=', $request->g);
                }
            })->paginate(10);
            return response()->json([
                "status" => "success",
                "data"   => $data
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }

    }
    public function home()
    {
        $dataNewest = Book::orderBy('created_at')->with('genreBook.genre')->paginate(12);
        $genre = Genre::paginate(12);
        return response()->json([
            "status" => "success",
            "data"   => $dataNewest,
            "genre"   => $genre,
        ]);
    }
 
    public function getBookStore(Request $request,$id)
    {   
        try {
            $search="";
            $data = Book::where('store_id',$id)->where(function ($query) use ($search) {
                $query->where('name','like',"%".$search."%")->
                orWhere('writter','like',"%".$search."%")->
                orWhere('publisher','like',"%".$search."%")
              ;})->with('genreBook.genre')->
            paginate(10);
            return response()->json([
                "status" => "success",
                "data"   => $data
            ]);
        } catch (\Throwable $th) {
            response()->json([
                "status" => "success",
                "data"   => ""
            ],400);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'name' => 'required|string|max:60',
			'description' => 'required',
			'isbn' => 'required',
			'page' => 'required|integer',
			'publication_year' => 'required|integer|digits:4',
			'publisher' => 'required|string',
			'writter' => 'required|string',
			'price' => 'required|integer',
			'discount' => 'required|integer',
			'photo' => 'file',
			'stock' => 'required|integer',
			'genre.*' => 'required|integer',
        ]);
        try {
            if(count($request->genre)<0){
                return response()->json([
                    'status'	=> "failed",
                    'fail'	    => "genre",
                    'message'	=> "Enter genre"
                ],400);
            }
            //code...
        } catch (\Throwable $th) {
            return response()->json([
                'status'	=> "failed",
                'fail'	    => "genre",
                'message'	=> "Enter genre"
            ],400);            
        }
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
            $book = new Book();
            $book->name 	        = $request->name;
            $book->description 	    = $request->description;
            $book->isbn 	        = $request->isbn;
            $book->page 	        = $request->page;
            $book->publication_year = $request->publication_year;
            $book->writter          = $request->writter;
            $book->publisher        = $request->publisher;
            $book->price            = $request->price;
            $book->discount         = $request->discount;
            $book->stock            = $request->stock;
            $book->store_id         = Store::where('user_id',$user->id)->first()->id ;
            $book->save();
                foreach($request->input('genre') as $key => $value) {
                    GenreBook::create([
                       'genre_id'=> $value,
                       'book_id' => $book->id
                    ]);
                }
            return response()->json([
                'status'	=> 'success',
                'message'	=> 'Buku berhasil ditambahkan'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status'	=> 'failed',
                'message'	=> 'Buku gagal teregistrasi'
            ], 400);
        }
    }


    public function show($id)
    {
        $data = Book::findOrFail($id);
        return response($data);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
            $validator = Validator::make($request->all(), [
                'name'             => 'string|max:60',
                'isbn'              => 'max:30',
                'page'              => 'integer',
                'publication_year'  => 'integer|digits:4',
                'publisher'         => 'string',
                'writter'           => 'string',
                'price'             => 'integer',
                'discount'          => 'integer',
                'photo'             => 'file',
                'stock'             => 'integer',
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'status'	=> 0,
                    'message'	=> $validator->errors()->toJson()
                ]);
            }
            try {
                $user = JWTAuth::parseToken()->authenticate();
                try {
                    $book = Book::findOrFail($id);
                } catch (\Throwable $th) {
                    return response()->json([
                        "status" => "failed",
                        "message"=> "Book Not Found"
                    ], 404);;
                }
                if(! $user->id == $book->store_id){
                    return response()->json([
                        "status" => "failed",
                        "message"=> "forbiden"
                    ], 403);;
                }
            } catch (\Throwable $th) {
                return response()->json([
                    "status" => "failed",
                    "message"=> "Not Authenticated"
                ], 401);
            }
    
            try {
                if($request->input('name')){
                    $book->name 	        = $request->name;
                }
                if($request->input('description')){
                    $book->description 	    = $request->description;
                }
                if($request->input('writter')){
                    $book->writter 	        = $request->writter;
                }
                if($request->input('isbn')){
                    $book->isbn 	        = $request->isbn;
                }
                if($request->input('page')){
                    $book->page 	        = $request->page;
                }
                if($request->input('publication_year')){
                    $book->publication_year = $request->publication_year;
                }
                if($request->input('publisher')){
                    $book->publisher        = $request->publisher;
                }
                if($request->input('price')){
                    $book->price            = $request->price;
                }
                if($request->input('discount')){
                    $book->discount         = $request->discount;
                }
                if($request->input('stock')){
                    $book->stock            = $request->stock;
                }
                $book->save();
    
                return response()->json([
                    'status'	=> 'success',
                    'message'	=> 'Buku berhasil dirubah'
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    'status'	=> 'failed',
                    'message'	=> 'Buku gagal dirubah'
                ], 201);
            }
    }


    public function delete($id)
    {
        try{

            Book::findOrFail($id)->delete();

            return response([
            	"status"	=> 1,
                "message"   => "Buku berhasil dihapus."
            ]);
        } catch(\Exception $e){
            return response([
            	"status"	=> 0,
                "message"   => $e->getMessage()
            ]);
        }
    }
    public function getAllGenre()
    {
        try {
            $genre = Genre::get();
            return response([
            	"status"	=> "success",
                "genre"   => $genre
            ],200); 
        } catch (\Throwable $th) {
            return response([
            	"status"	=> 0,
                "message"   => "failed"
            ]);
        }
    }
    function getGenre(Request $request)
    {
        try {
            if($request->input('s')){
                $genre = Genre::where('pegawai_nama','like',"%".$request->s."%")->paginate(10);
            }
            $genre = Genre::paginate(10);
            return response([
            	"status"	=> "success",
                "genre"   => $genre
            ],200); 
        } catch (\Exception $th) {
            return response([
            	"status"	=> "failed",
                "genre"   => ""
            ],500);
        }
    }
}
