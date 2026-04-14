<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marble_pageviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('language_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('path', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('session_id', 64)->nullable()->index();
            $table->ipAddress('ip')->nullable();
            $table->string('country', 2)->nullable();
            $table->timestamp('created_at')->nullable()->index();

            $table->foreign('item_id')->references('id')->on('items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marble_pageviews');
    }
};
