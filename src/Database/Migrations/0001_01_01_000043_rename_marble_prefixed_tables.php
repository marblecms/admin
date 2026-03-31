<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marble_api_tokens')) {
            Schema::rename('marble_api_tokens', 'api_tokens');
        }
        if (Schema::hasTable('marble_redirects')) {
            Schema::rename('marble_redirects', 'redirects');
        }
        if (Schema::hasTable('marble_settings')) {
            Schema::rename('marble_settings', 'settings');
        }
    }

    public function down(): void
    {
        Schema::rename('api_tokens', 'marble_api_tokens');
        Schema::rename('redirects',  'marble_redirects');
        Schema::rename('settings',   'marble_settings');
    }
};
