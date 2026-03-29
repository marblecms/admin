<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Allow a "default" site with no specific domain
            $table->string('domain')->nullable()->change();
            $table->boolean('is_default')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('is_default');
            $table->string('domain')->nullable(false)->change();
        });
    }
};
