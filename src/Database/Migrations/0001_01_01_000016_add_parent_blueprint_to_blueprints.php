<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->foreignId('parent_blueprint_id')
                ->nullable()
                ->after('locked')
                ->constrained('blueprints')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->dropForeign(['parent_blueprint_id']);
            $table->dropColumn('parent_blueprint_id');
        });
    }
};
