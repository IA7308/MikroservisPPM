<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class ProfileAuthor extends Model
{
    use HasFactory;
    protected $table ='profile_authors';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'programs_id',
        'affiliation_id',
        'nidn',
        'fullname',
        'country',
        'academic_grade_raw',
        'academic_grade',
        'gelar_depan',
        'gelar_belakang',
        'last_education',
        'sinta_score_v2_overall',
        'sinta_score_v2_3year',
        'sinta_score_v3_overall',
        'sinta_score_v3_3year',
        'affiliation_score_v3_overall',
        'affiliation_score_v3_3year',
        'image',
    ];

    public function docBookAuthors()
    {
        $docBookAuthorServiceUrl = config('services.book.url');
        $response = Http::get("{$docBookAuthorServiceUrl}/api/books", [
            'author_id' => $this->id
        ]);

        return $response->successful() 
            ? $response->json() 
            : [];
    }

    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class, "affiliation_id")->select("id", "code_pddikti", "name");
    }

    public function program()
    {
        return $this->belongsTo(ProfileProgram::class, "programs_id", "code_pddikti")->select("code_pddikti", "faculty_id", "level", "name_id", "name_en");
    }

    // public function docGarudaAuthors() {
    //     return $this->hasMany(Garuda::class, 'author_id', 'id');
    // }
}
