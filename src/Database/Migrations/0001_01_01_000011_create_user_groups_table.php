<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('entry_item_id')->nullable()->constrained('items')->nullOnDelete();

            // Permissions
            $table->boolean('can_create_users')->default(false);
            $table->boolean('can_edit_users')->default(false);
            $table->boolean('can_delete_users')->default(false);
            $table->boolean('can_list_users')->default(false);
            $table->boolean('can_create_blueprints')->default(false);
            $table->boolean('can_edit_blueprints')->default(false);
            $table->boolean('can_delete_blueprints')->default(false);
            $table->boolean('can_list_blueprints')->default(false);
            $table->boolean('can_create_groups')->default(false);
            $table->boolean('can_edit_groups')->default(false);
            $table->boolean('can_delete_groups')->default(false);
            $table->boolean('can_list_groups')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_groups');
    }
};
