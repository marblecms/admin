<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft | published | rolled_back
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('content_bundle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained('content_bundles')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('pre_publish_status', 20)->nullable();  // item status before bundle publish
            $table->foreignId('pre_publish_revision_id')->nullable()->constrained('item_revisions')->nullOnDelete();
            $table->unique(['bundle_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_bundle_items');
        Schema::dropIfExists('content_bundles');
    }
};
