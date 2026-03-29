<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier')->unique();
            $table->string('icon')->default('');
            $table->foreignId('blueprint_group_id')->constrained('blueprint_groups')->cascadeOnDelete();
            $table->boolean('allow_children')->default(true);
            $table->boolean('list_children')->default(false);
            $table->boolean('show_in_tree')->default(true);
            $table->boolean('locked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprints');
    }
};
