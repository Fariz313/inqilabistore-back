<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $table = 'book';
    public function genreBook()
    {
        return $this->hasMany(GenreBook::class,'book_id');
    }
}
