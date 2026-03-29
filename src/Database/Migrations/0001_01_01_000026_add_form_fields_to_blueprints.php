<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->boolean('is_form')->default(false)->after('schedulable');
            $table->string('form_recipients')->nullable()->after('is_form');   // comma-separated emails
            $table->string('form_success_message')->nullable()->after('form_recipients');
        });
    }

    public function down(): void
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->dropColumn(['is_form', 'form_recipients', 'form_success_message']);
        });
    }
};
