@php $val = is_array($value) ? $value : ['date' => '', 'time' => '']; @endphp
<br />
<input type="text" class="form-control datepicker marble-date-input" name="fields[{{ $field->id }}][{{ $languageId }}][date]" value="{{ $val['date'] ?? '' }}" data-date-format="dd.mm.yyyy" />
<input type="text" class="form-control marble-time-input" name="fields[{{ $field->id }}][{{ $languageId }}][time]" value="{{ $val['time'] ?? '' }}" placeholder="HH:MM" />
<div class="clearfix"></div>
<br />
