<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->unsignedBigInteger('settings_blueprint_id')->nullable()->after('settings_item_id');
            $table->foreign('settings_blueprint_id')->references('id')->on('blueprints')->nullOnDelete();
        });

        Schema::table('blueprints', function (Blueprint $table) {
            $table->boolean('hide_system_fields')->default(false)->after('is_form');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['settings_blueprint_id']);
            $table->dropColumn('settings_blueprint_id');
        });
        Schema::table('blueprints', function (Blueprint $table) {
            $table->dropColumn('hide_system_fields');
        });
    }
};
