<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marble_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_path')->unique()->index();
            $table->string('target_path')->nullable();
            $table->foreignId('target_item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marble_redirects');
    }
};
