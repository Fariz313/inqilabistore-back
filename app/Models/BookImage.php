<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookImage extends Model
{
    use HasFactory;
    protected $table = 'book_image';
    protected $fillable = [
        'book_id', 'path'
    ];
    public function genreBook()
    {
        return $this->belongTo(Book::class);
    }
}
