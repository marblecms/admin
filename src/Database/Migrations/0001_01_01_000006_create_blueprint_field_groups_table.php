<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_field_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_field_groups');
    }
};
