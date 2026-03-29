@php $rows = $field->configuration['rows'] ?? 10; @endphp
<textarea class="form-control rich-text-editor" rows="{{ $rows }}" name="fields[{{ $field->id }}][{{ $languageId }}]">{{ $value }}</textarea>
