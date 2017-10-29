<select class="form-control" name="attributes[{{$attribute->id}}][{{$locale}}]">
    @foreach($attribute->classAttribute->configuration as $key => $row)
        <option value="{{$row["key"]}}" {{ $attribute->value[$locale] == $row["key"] ? "selected" : "" }}>{{$row["value"]}}</option>
    @endforeach
</select>