@php $val = is_array($value) ? $value : ['hour' => '', 'minute' => '']; @endphp
<br />
<input type="text" style="width:50px;display:inline-block;" name="fields[{{ $field->id }}][{{ $languageId }}][hour]" value="{{ $val['hour'] ?? '' }}" class="form-control" placeholder="HH" />
:
<input type="text" style="width:50px;display:inline-block;" name="fields[{{ $field->id }}][{{ $languageId }}][minute]" value="{{ $val['minute'] ?? '' }}" class="form-control" placeholder="MM" />
<div class="clearfix"></div>
<br />
