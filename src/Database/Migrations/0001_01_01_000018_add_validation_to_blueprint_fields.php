<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blueprint_fields', function (Blueprint $table) {
            $table->string('validation_rules')->nullable()->after('locked');
        });
    }

    public function down(): void
    {
        Schema::table('blueprint_fields', function (Blueprint $table) {
            $table->dropColumn('validation_rules');
        });
    }
};
