<div class="form-group">
    <label><b>{{ trans('marble::admin.on_delete') }}</b></label>
    <select name="configuration[{{ $field->id }}][on_delete]" class="form-control">
        <option value="detach"  {{ ($field->configuration['on_delete'] ?? 'detach') === 'detach'  ? 'selected' : '' }}>{{ trans('marble::admin.on_delete_detach') }}</option>
        <option value="restrict" {{ ($field->configuration['on_delete'] ?? '') === 'restrict' ? 'selected' : '' }}>{{ trans('marble::admin.on_delete_restrict') }}</option>
        <option value="cascade" {{ ($field->configuration['on_delete'] ?? '') === 'cascade' ? 'selected' : '' }}>{{ trans('marble::admin.on_delete_cascade') }}</option>
    </select>
    <p class="help-block">{{ trans('marble::admin.on_delete_hint') }}</p>
</div>
