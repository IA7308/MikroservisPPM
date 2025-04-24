<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profile_authors', function (Blueprint $table) {
            $table->id();
            $table->integer('programs_id');
            $table->integer('affiliation_id');
            $table->string('nidn');
            $table->string('fullname');
            $table->string('country');
            $table->string('academic_grade_raw');
            $table->string('academic_grade');
            $table->string('gelar_depan');
            $table->string('gelar_belakang');
            $table->string('last_education');
            $table->float('sinta_score_v2_overall');
            $table->float('sinta_score_v2_3year');
            $table->float('sinta_score_v3_overall');
            $table->float('sinta_score_v3_3year');
            $table->float('affiliation_score_v3_overall');
            $table->float('affiliation_score_v3_3year');
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_authors');
    }
};
