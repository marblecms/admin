<input type="checkbox" onchange="$(this).parent().find('input[type=hidden]').val($(this).prop('checked') ? 1 : 0)" {{ $value == 1 ? 'checked' : '' }} value="1" class="form-control" />
<input type="hidden" name="fields[{{ $field->id }}][{{ $languageId }}]" value="{{ $value }}" class="form-control" />
