@php $config = $field->config ?? []; @endphp
<select
    id="field_{{ $field->id }}"
    name="fields[{{ $field->id }}][{{ $langId }}]{{ !empty($config['multiple']) ? '[]' : '' }}"
    class="{{ $inputClass ?? '' }}"
    {{ !empty($config['multiple']) ? 'multiple' : '' }}>
    @foreach($config['options'] ?? [] as $opt)
        <option value="{{ $opt['key'] }}">{{ $opt['value'] }}</option>
    @endforeach
</select>
