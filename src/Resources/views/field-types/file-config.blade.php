<div class="form-group">
    <label><b>{{ trans('marble::admin.allowed_filetypes') }}</b></label>
    <input type="text"
           name="configuration[{{ $field->id }}][allowed_filetypes]"
           value="{{ $field->configuration['allowed_filetypes'] ?? '' }}"
           class="form-control"
           placeholder="pdf,docx,xlsx — {{ trans('marble::admin.allowed_filetypes_hint') }}" />
</div>
