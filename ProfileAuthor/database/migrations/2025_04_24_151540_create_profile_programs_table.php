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
        Schema::create('profile_programs', function (Blueprint $table) {
            $table->id();
            $table->integer('faculty_id');
            $table->integer('code_pddikti');
            $table->string('name_id');
            $table->string('name_en');
            $table->string('level');
            $table->string('website');
            $table->string('affiliation_id');
            $table->decimal('sinta_score_v3_overall');
            $table->decimal('sinta_score_v3_3year');
            $table->decimal('sinta_score_v3_productivity_overall');
            $table->decimal('sinta_score_v3_productivity_3year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_programs');
    }
};
