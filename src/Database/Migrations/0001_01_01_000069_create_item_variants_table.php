<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('name', 128)->default('Variant B');
            $table->unsignedTinyInteger('traffic_split')->default(50); // % sent to variant
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('impressions_a')->default(0);
            $table->unsignedBigInteger('impressions_b')->default(0);
            $table->timestamps();
        });

        Schema::create('item_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('item_variants')->cascadeOnDelete();
            $table->foreignId('blueprint_field_id')->constrained('blueprint_fields')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->longText('value')->nullable();
            $table->unique(['variant_id', 'blueprint_field_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_variant_values');
        Schema::dropIfExists('item_variants');
    }
};
