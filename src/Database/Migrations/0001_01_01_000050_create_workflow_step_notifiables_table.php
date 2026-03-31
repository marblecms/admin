<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_step_notifiables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_step_id')->constrained('workflow_steps')->cascadeOnDelete();
            $table->string('notifiable_type'); // 'user' or 'group'
            $table->unsignedBigInteger('notifiable_id');
            $table->string('channel')->default('cms'); // 'cms', 'email', 'both'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_step_notifiables');
    }
};
