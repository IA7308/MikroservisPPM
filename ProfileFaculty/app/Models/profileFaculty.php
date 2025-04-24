<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class profileFaculty extends Model
{
    use HasFactory;
    protected $table ='profile_faculties';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name_id',
        'name_en',
        'website'
    ];

    // public function programs()
    // {
    //     return $this->hasMany(ProfileProgram::class, 'faculty_id', 'id');
    // }
}
