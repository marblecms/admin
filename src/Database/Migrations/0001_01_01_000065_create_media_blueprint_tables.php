<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rules: mime pattern → blueprint assignment
        Schema::create('media_blueprint_rules', function (Blueprint $table) {
            $table->id();
            $table->string('mime_pattern');         // e.g. "image/*", "application/pdf", "video/mp4"
            $table->foreignId('blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Per-media field values (mirrors item_values)
        Schema::create('media_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('blueprint_field_id')->constrained('blueprint_fields')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['media_id', 'blueprint_field_id', 'language_id']);
        });

        // Which blueprint was assigned to a media item
        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('blueprint_id')->nullable()->constrained('blueprints')->nullOnDelete()->after('media_folder_id');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['blueprint_id']);
            $table->dropColumn('blueprint_id');
        });
        Schema::dropIfExists('media_values');
        Schema::dropIfExists('media_blueprint_rules');
    }
};
