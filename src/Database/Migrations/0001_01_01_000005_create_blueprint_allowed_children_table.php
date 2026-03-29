<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_allowed_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->foreignId('child_blueprint_id')->nullable()->constrained('blueprints')->cascadeOnDelete();
            $table->boolean('allow_all')->default(false);

            $table->unique(['blueprint_id', 'child_blueprint_id'], 'bp_allowed_children_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_allowed_children');
    }
};
