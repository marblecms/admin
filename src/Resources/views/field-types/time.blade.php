@php $val = is_array($value) ? $value : ['hour' => '', 'minute' => '']; @endphp
<br />
<input type="text" class="form-control marble-hour-input" name="fields[{{ $field->id }}][{{ $languageId }}][hour]" value="{{ $val['hour'] ?? '' }}" placeholder="HH" />
:
<input type="text" class="form-control marble-hour-input" name="fields[{{ $field->id }}][{{ $languageId }}][minute]" value="{{ $val['minute'] ?? '' }}" placeholder="MM" />
<div class="clearfix"></div>
<br />
