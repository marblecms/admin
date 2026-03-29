<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier');
            $table->foreignId('blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->foreignId('field_type_id')->constrained('field_types');
            $table->foreignId('blueprint_field_group_id')->nullable()->constrained('blueprint_field_groups')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->json('configuration')->nullable();
            $table->boolean('translatable')->default(false);
            $table->boolean('locked')->default(false);

            $table->unique(['blueprint_id', 'identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_fields');
    }
};
