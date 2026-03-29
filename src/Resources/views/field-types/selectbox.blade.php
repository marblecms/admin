@php $options = $field->configuration['options'] ?? []; @endphp
<select class="form-control" name="fields[{{ $field->id }}][{{ $languageId }}]">
    @foreach($options as $option)
        <option value="{{ $option['key'] }}" {{ $value == $option['key'] ? 'selected' : '' }}>{{ $option['value'] }}</option>
    @endforeach
</select>
