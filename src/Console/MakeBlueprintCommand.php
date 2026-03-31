<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Marble\Admin\FieldTypeRegistry;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\BlueprintField;
use Marble\Admin\Models\FieldType;

class MakeBlueprintCommand extends Command
{
    protected $signature   = 'marble:make-blueprint';
    protected $description = 'Interactively create a new Blueprint with fields and generate a frontend view template';

    public function handle(FieldTypeRegistry $registry): int
    {
        $this->newLine();
        $this->line('<fg=blue>╔══════════════════════════════╗</>');
        $this->line('<fg=blue>║   Marble Blueprint Generator  ║</>');
        $this->line('<fg=blue>╚══════════════════════════════╝</>');
        $this->newLine();

        // ── Blueprint basics ──────────────────────────────────────────────
        $name = $this->ask('Blueprint name (human readable, e.g. "Blog Post")');
        if (empty($name)) {
            $this->error('Name is required.');
            return self::FAILURE;
        }

        $suggestedIdentifier = Str::snake(Str::slug($name, '_'));
        $identifier = $this->ask("Identifier (snake_case, used in code)", $suggestedIdentifier);
        $identifier = Str::snake($identifier);

        if (Blueprint::where('identifier', $identifier)->exists()) {
            $this->error("A blueprint with identifier '{$identifier}' already exists.");
            return self::FAILURE;
        }

        $allowChildren = $this->confirm('Allow child items?', false);
        $apiPublic     = $this->confirm('Expose via public REST API?', false);
        $versionable   = $this->confirm('Enable revision history?', true);

        // ── Fields ────────────────────────────────────────────────────────
        $availableTypes = collect($registry->all())->map(fn ($ft) => $ft->identifier())->sort()->values()->all();

        $fields = [];
        $this->newLine();
        $this->line('<comment>Now add fields. Press Enter with an empty name to finish.</comment>');

        $sortOrder = 1;
        while (true) {
            $this->newLine();
            $fieldName = $this->ask('Field label (e.g. "Teaser Text") — empty to finish');
            if (empty($fieldName)) {
                break;
            }

            $fieldIdentifier = $this->ask('Field identifier', Str::snake(Str::slug($fieldName, '_')));
            $fieldType       = $this->choice('Field type', $availableTypes, 'textfield');
            $translatable    = $this->confirm('Translatable?', true);
            $required        = $this->confirm('Required?', false);

            $fields[] = [
                'name'         => $fieldName,
                'identifier'   => $fieldIdentifier,
                'type'         => $fieldType,
                'translatable' => $translatable,
                'required'     => $required,
                'sort_order'   => $sortOrder++,
            ];

            $this->line("  <info>✓ Added:</info> {$fieldIdentifier} ({$fieldType})");
        }

        // ── Summary ───────────────────────────────────────────────────────
        $this->newLine();
        $this->line('<comment>Summary:</comment>');
        $this->line("  Name       : {$name}");
        $this->line("  Identifier : {$identifier}");
        $this->line("  Fields     : " . count($fields));
        foreach ($fields as $f) {
            $trans = $f['translatable'] ? 'translatable' : 'global';
            $req   = $f['required'] ? ', required' : '';
            $this->line("               - {$f['identifier']} ({$f['type']}, {$trans}{$req})");
        }
        $this->newLine();

        if (!$this->confirm('Create blueprint?', true)) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        // ── Create blueprint ──────────────────────────────────────────────
        $blueprint = Blueprint::create([
            'name'          => $name,
            'identifier'    => $identifier,
            'allow_children'=> $allowChildren,
            'api_public'    => $apiPublic,
            'versionable'   => $versionable,
            'show_in_tree'  => true,
            'locked'        => false,
            'list_children' => false,
            'schedulable'   => false,
            'is_form'       => false,
        ]);

        // Sync field types to DB (ensures FieldType records exist)
        $this->call('marble:sync-field-types', [], $this->output);

        // ── Create fields ─────────────────────────────────────────────────
        foreach ($fields as $field) {
            $fieldTypeRecord = FieldType::where('identifier', $field['type'])->first();

            BlueprintField::create([
                'blueprint_id'     => $blueprint->id,
                'field_type_id'    => $fieldTypeRecord?->id,
                'name'             => $field['name'],
                'identifier'       => $field['identifier'],
                'translatable'     => $field['translatable'],
                'locked'           => false,
                'validation_rules' => $field['required'] ? 'required' : null,
                'sort_order'       => $field['sort_order'],
            ]);
        }

        $this->line("<info>✓ Blueprint '{$name}' created (ID: {$blueprint->id})</info>");

        // ── Generate view template ─────────────────────────────────────────
        $viewPath = resource_path("views/marble-pages/{$identifier}.blade.php");

        if (file_exists($viewPath)) {
            $this->warn("  View already exists, skipping: {$viewPath}");
        } else {
            $stub = $this->generateViewStub($identifier, $name, $fields);
            if (!is_dir(dirname($viewPath))) {
                mkdir(dirname($viewPath), 0755, true);
            }
            file_put_contents($viewPath, $stub);
            $this->line("<info>✓ View created:</info> resources/views/marble-pages/{$identifier}.blade.php");
        }

        $this->newLine();
        $this->line('<fg=green>All done!</>');
        $this->line("  Admin URL : /" . config('marble.route_prefix', 'admin') . "/blueprint/edit/{$blueprint->id}");
        $this->newLine();

        return self::SUCCESS;
    }

    private function generateViewStub(string $identifier, string $name, array $fields): string
    {
        $fieldLines = '';
        foreach ($fields as $field) {
            $fieldLines .= "\n    {{-- {$field['name']} --}}\n";
            $fieldLines .= "    <x-marble::value :item=\"\$item\" field=\"{$field['identifier']}\" />\n";
        }

        return <<<BLADE
@extends(config('marble.frontend_layout', 'layouts.app'))

@section('title', \$item->name())

@section('content')
    <article>
        <h1>{{ \$item->name() }}</h1>
{$fieldLines}
    </article>
@endsection
BLADE;
    }
}
