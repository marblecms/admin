<input type="hidden" name="fields[{{ $field->id }}][{{ $langId }}]" value="0">
<label class="marble-check-label">
    <input type="checkbox"
        id="field_{{ $field->id }}"
        name="fields[{{ $field->id }}][{{ $langId }}]"
        value="1">
    {{ $field->name }}
</label>
