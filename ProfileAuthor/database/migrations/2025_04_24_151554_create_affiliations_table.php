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
        Schema::create('affiliations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('code_pddikti');
            $table->string('name');
            $table->string('abbreviation');
            $table->string('country');
            $table->integer('korwil_scope');
            $table->integer('lidikti_scope');
            $table->string('website');
            $table->string('description');
            $table->bigInteger('sinta_score_v2_overall');
            $table->bigInteger('sinta_score_v2_3year');
            $table->decimal('sinta_score_v2_productivity_overall');
            $table->decimal('sinta_score_v2_productivity_3year');
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
        Schema::dropIfExists('affiliations');
    }
};


