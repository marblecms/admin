<select class="form-control" name="attributes[{{$attribute->id}}][{{$locale}}]">
    <option value="" {{ $attribute->value[$locale] == "" ? "selected" : "" }}>default</option>
    @foreach (glob(resource_path().'/views/layouts/*.php') as $filename)
        <option value="{{basename($filename)}}" {{ $attribute->value[$locale] == basename($filename) ? "selected" : "" }}>{{basename($filename)}}</option>
    @endforeach
</select>