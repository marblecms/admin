<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_group_allowed_blueprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->foreignId('blueprint_id')->nullable()->constrained('blueprints')->cascadeOnDelete();
            $table->boolean('allow_all')->default(false);

            $table->unique(['user_group_id', 'blueprint_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_group_allowed_blueprints');
    }
};
