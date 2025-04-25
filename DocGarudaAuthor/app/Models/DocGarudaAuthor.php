<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class DocGarudaAuthor extends Model
{
    use HasFactory;

    protected $table ='doc_garuda_authors';
    public $timestamps = true;
    protected $primaryKey = 'id';

        protected $fillable = [
        'id',
        'author_id',
        'author_order',
        'accreditation',
        'title',
        'abstract',
        'publisher_name',
        'publish_date',
        'publish_year',
        'doi',
        'citation',
        'source',
        'source_issue',
        'source_page',
        'url'
    ];

    public function getProfileAuthorAttribute()
    {
        $profileAuthorServiceUrl = config('services.profile_author.url');
        $response = Http::get("{$profileAuthorServiceUrl}/api/profile-author/" . $this->author_id);
        return $response->json();
    } 
}
