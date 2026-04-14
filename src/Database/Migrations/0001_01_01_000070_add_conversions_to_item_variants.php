<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('conversions_a')->default(0)->after('impressions_b');
            $table->unsignedBigInteger('conversions_b')->default(0)->after('conversions_a');
        });
    }

    public function down(): void
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->dropColumn(['conversions_a', 'conversions_b']);
        });
    }
};
