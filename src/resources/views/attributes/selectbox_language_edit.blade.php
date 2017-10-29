<select class="form-control" name="attributes[{{$attribute->id}}][{{$locale}}]">
    @foreach(\App\Language::all() as $language)
        <option value="{{$language->id}}" {{ $attribute->value[$locale] == $language->id ? "selected" : "" }}>{{$language->name}}</option>
    @endforeach
</select>