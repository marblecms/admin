<input type="checkbox" onchange="$(this).parent().find('input[type=hidden]').val($(this).prop('checked') ? 1 : 0)" {{$attribute->value[$locale] == 1 ? "checked" : ""}} value="1" class="form-control" />

<input type="hidden" name="attributes[{{$attribute->id}}][{{$locale}}]" value="{{$attribute->value[$locale]}}" class="form-control" />