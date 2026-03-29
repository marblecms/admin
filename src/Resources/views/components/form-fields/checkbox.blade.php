<input type="hidden" name="fields[{{ $field->id }}][{{ $langId }}]" value="0">
<label style="display:inline-flex;align-items:center;gap:6px;font-weight:normal">
    <input type="checkbox"
        id="field_{{ $field->id }}"
        name="fields[{{ $field->id }}][{{ $langId }}]"
        value="1">
    {{ $field->name }}
</label>
