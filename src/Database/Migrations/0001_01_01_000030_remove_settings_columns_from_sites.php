<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['settings_item_id']);
            $table->dropForeign(['settings_blueprint_id']);
            $table->dropColumn(['settings_item_id', 'settings_blueprint_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->unsignedBigInteger('settings_item_id')->nullable();
            $table->unsignedBigInteger('settings_blueprint_id')->nullable();
        });
    }
};
