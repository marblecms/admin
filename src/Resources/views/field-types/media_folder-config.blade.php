<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label><b>{{ trans('marble::admin.file_type_filter') }}</b></label>
            <select name="configuration[{{ $field->id }}][file_type]" class="form-control">
                @foreach(['all' => trans('marble::admin.all_files'), 'image' => trans('marble::admin.images_only'), 'pdf' => 'PDF'] as $val => $label)
                    <option value="{{ $val }}" {{ ($field->configuration['file_type'] ?? 'all') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4 marble-pt-lg">
        <label class="checkbox-inline marble-check-label">
            <input type="hidden" name="configuration[{{ $field->id }}][recursive]" value="0">
            <input type="checkbox" name="configuration[{{ $field->id }}][recursive]" value="1"
                   {{ !empty($field->configuration['recursive']) ? 'checked' : '' }}>
            {{ trans('marble::admin.include_subfolders') }}
        </label>
    </div>
</div>
