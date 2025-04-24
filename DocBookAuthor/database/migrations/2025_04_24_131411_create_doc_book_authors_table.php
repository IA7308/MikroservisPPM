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
        Schema::create('doc_book_authors', function (Blueprint $table) {
            $table->id();
            $table->integer('author_id');
            $table->string('category');
            $table->integer('isbn');
            $table->string('title');
            $table->string('authors');
            $table->string('place');
            $table->string('publisher');
            $table->integer('year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_book_authors');
    }
};
