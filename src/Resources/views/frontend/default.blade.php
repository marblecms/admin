<!DOCTYPE html>
<html>
<head>
    <title>{{ $item->name() }}</title>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 0 20px; color: #333; }
        .marble-debug { background: #f8f8f8; border: 1px solid #ddd; padding: 16px; border-radius: 4px; font-size: 13px; }
        .marble-debug h3 { margin: 0 0 12px; color: #666; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .marble-debug table { width: 100%; border-collapse: collapse; }
        .marble-debug td { padding: 4px 8px 4px 0; vertical-align: top; }
        .marble-debug td:first-child { width: 140px; color: #999; }
        .marble-form-field { margin-bottom: 16px; }
        .marble-form-field label { display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px; }
        .marble-form-field input[type=text],
        .marble-form-field input[type=date],
        .marble-form-field input[type=datetime-local],
        .marble-form-field input[type=time],
        .marble-form-field select,
        .marble-form-field textarea { width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 3px; font-size: 14px; box-sizing: border-box; }
        .marble-form-field textarea { min-height: 100px; resize: vertical; }
        .marble-form-success { background: #eafaf1; border: 1px solid #27ae60; color: #1a7a43; padding: 12px 16px; border-radius: 3px; margin-bottom: 16px; }
        button[type=submit] { background: #2258A8; color: #fff; border: none; padding: 10px 24px; border-radius: 3px; font-size: 14px; cursor: pointer; }
        button[type=submit]:hover { background: #163C80; }
    </style>
</head>
<body>
    <h1>{{ $item->name() }}</h1>

    @if($item->blueprint->is_form)
        <x-marble::marble-form :item="$item" />
    @else
        <div class="marble-debug">
            <h3>Marble — no view found for blueprint "{{ $item->blueprint->identifier }}"</h3>
            <p>Create <code>resources/views/marble-pages/{{ $item->blueprint->identifier }}.blade.php</code> to replace this fallback.</p>
            <table>
                <tr><td>Item ID</td><td>{{ $item->id }}</td></tr>
                <tr><td>Blueprint</td><td>{{ $item->blueprint->name }} ({{ $item->blueprint->identifier }})</td></tr>
                <tr><td>Status</td><td>{{ $item->status }}</td></tr>
                <tr><td>Slug</td><td>{{ $item->slug() ?: '(root)' }}</td></tr>
                @foreach($item->blueprint->fields as $field)
                    <tr>
                        <td>{{ $field->name }}</td>
                        <td>@include($field->fieldTypeInstance()->frontendComponent(), ['value' => $item->value($field->identifier), 'field' => $field, 'item' => $item])</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif
</body>
</html>
