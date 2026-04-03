<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // item_values: standalone item_id index for fast value loading per item
        Schema::table('item_values', function (Blueprint $table) {
            $table->index('item_id');
            $table->index('blueprint_field_id');
            $table->index('language_id');
        });

        // blueprint_fields: index on blueprint_id for allFields() queries
        Schema::table('blueprint_fields', function (Blueprint $table) {
            $table->index('blueprint_id');
        });

        // items: index on parent_id for children() queries
        Schema::table('items', function (Blueprint $table) {
            $table->index('parent_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('item_values', function (Blueprint $table) {
            $table->dropIndex(['item_id']);
            $table->dropIndex(['blueprint_field_id']);
            $table->dropIndex(['language_id']);
        });

        Schema::table('blueprint_fields', function (Blueprint $table) {
            $table->dropIndex(['blueprint_id']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['status']);
        });
    }
};
