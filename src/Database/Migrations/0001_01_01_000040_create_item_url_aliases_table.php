<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_url_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('alias');
            $table->timestamps();

            $table->unique(['alias', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_url_aliases');
    }
};
