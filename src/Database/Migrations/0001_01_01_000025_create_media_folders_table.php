<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('media_folder_id')->nullable()->after('id')
                  ->constrained('media_folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media', fn (Blueprint $t) => $t->dropColumn('media_folder_id'));
        Schema::dropIfExists('media_folders');
    }
};
