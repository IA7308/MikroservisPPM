<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class docBookAuthor extends Model
{
    use HasFactory;
    protected $table ='doc_book_authors';
    public $timestamps = true;
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'author_id',
        'category',
        'isbn',
        'title',
        'authors',
        'place',
        'publisher',
        'year'
    ];

    public function getProfileAuthorAttribute()
    {
        $response = Http::get('http://127.0.0.1/api/profile-author/' . $this->author_id);
        return $response->json();
    }
}
