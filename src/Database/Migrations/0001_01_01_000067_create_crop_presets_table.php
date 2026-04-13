<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->unique();   // machine identifier: hero, thumbnail, og_image
            $table->string('label', 128);            // human label
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_presets');
    }
};
