<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->boolean('reject_enabled')->default(false)->after('sort_order');
            $table->foreignId('reject_to_step_id')->nullable()->after('reject_enabled')
                ->constrained('workflow_steps')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropForeign(['reject_to_step_id']);
            $table->dropColumn(['reject_enabled', 'reject_to_step_id']);
        });
    }
};
