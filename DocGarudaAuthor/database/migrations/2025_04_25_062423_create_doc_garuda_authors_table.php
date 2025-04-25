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
        Schema::create('doc_garuda_authors', function (Blueprint $table) {
            $table->id();
            $table->integer('author_id');
            $table->string('author_order');
            $table->string('accreditation');
            $table->string('title');
            $table->string('abstract');
            $table->string('publisher_name');
            $table->string('publish_date');
            $table->integer('publish_year');
            $table->string('doi');
            $table->integer('citation');
            $table->string('source');
            $table->string('source_issue');
            $table->string('source_page');
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_garuda_authors');
    }
};
