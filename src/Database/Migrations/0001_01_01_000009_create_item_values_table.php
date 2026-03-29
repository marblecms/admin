<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('blueprint_field_id')->constrained('blueprint_fields')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->text('value')->nullable();

            $table->unique(['item_id', 'blueprint_field_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_values');
    }
};
