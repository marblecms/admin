<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier')->unique();
            $table->string('class'); // Fully qualified class name, e.g. Marble\Admin\FieldTypes\Textfield
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_types');
    }
};
