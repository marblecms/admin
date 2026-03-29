<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('language', 10)->default('en')->after('email');
        });
        Schema::table('blueprints', function (Blueprint $table) {
            $table->unsignedBigInteger('form_success_item_id')->nullable()->after('form_success_message');
            $table->foreign('form_success_item_id')->references('id')->on('items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->dropForeign(['form_success_item_id']);
            $table->dropColumn('form_success_item_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
};
