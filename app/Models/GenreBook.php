<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenreBook extends Model
{
    use HasFactory;
    protected $table = 'genre_book';
    protected $fillable = ['genre_id','book_id'];

    public function genre()
    {
        return $this->hasOne(Genre::class,'id','genre_id');
    }
}
