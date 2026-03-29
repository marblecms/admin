<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('blueprints');
            $table->foreignId('parent_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('path')->default('/')->index(); // Materialized path, e.g. /1/20/22
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('published'); // draft, published, archived
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
