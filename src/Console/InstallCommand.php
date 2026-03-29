<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature   = 'marble:install';
    protected $description = 'Run Marble migrations, sync field types, and seed the initial tree';

    public function handle(): int
    {
        $this->info('Running migrations...');
        $this->call('migrate');

        $this->info('Syncing field types...');
        $this->call('marble:sync-field-types');

        $this->info('Seeding initial data...');
        $this->call('db:seed', ['--class' => \Marble\Admin\Database\Seeders\MarbleSeeder::class]);

        $this->info('Publishing assets...');
        $this->call('vendor:publish', ['--tag' => 'marble-assets', '--force' => true]);

        $this->newLine();
        $this->info('✓ Marble installed successfully.');
        $this->line('  Admin URL : /' . config('marble.route_prefix', 'admin'));
        $this->line('  Email     : admin@marble.local');
        $this->line('  Password  : password');

        return self::SUCCESS;
    }
}
