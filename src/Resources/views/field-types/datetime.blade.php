@php $val = is_array($value) ? $value : ['date' => '', 'time' => '']; @endphp
<br />
<input type="text" style="width:100px;display:inline-block;" name="fields[{{ $field->id }}][{{ $languageId }}][date]" value="{{ $val['date'] ?? '' }}" class="form-control datepicker" data-date-format="dd.mm.yyyy" />
<input type="text" style="width:80px;display:inline-block;" name="fields[{{ $field->id }}][{{ $languageId }}][time]" value="{{ $val['time'] ?? '' }}" class="form-control" placeholder="HH:MM" />
<div class="clearfix"></div>
<br />
