@php $rows = $field->configuration['rows'] ?? 5; @endphp
<textarea class="form-control" rows="{{ $rows }}" name="fields[{{ $field->id }}][{{ $languageId }}]">{{ $value }}</textarea>
