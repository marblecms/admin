<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add CRUD permission flags to user_group_allowed_blueprints
        Schema::table('user_group_allowed_blueprints', function (Blueprint $table) {
            $table->boolean('can_create')->default(true)->after('allow_all');
            $table->boolean('can_read')->default(true)->after('can_create');
            $table->boolean('can_update')->default(true)->after('can_read');
            $table->boolean('can_delete')->default(false)->after('can_update');
        });
    }

    public function down(): void
    {
        Schema::table('user_group_allowed_blueprints', function (Blueprint $table) {
            $table->dropColumn(['can_create', 'can_read', 'can_update', 'can_delete']);
        });
    }
};
