@if($attribute->classAttribute->configuration)
    @foreach($attribute->classAttribute->configuration as $key => $row)
        <div class="pull-left">
            <div class="onoffswitch onoffswitch-success">
                <input type="checkbox" name="attributes[{{$attribute->id}}][{{$locale}}][{{$row["key"]}}]" class="onoffswitch-checkbox" value="1" id="onoff-node-{{$attribute->id}}-{{$locale}}-{{$key}}" {{ isset($attribute->value[$locale][$row["key"]]) && $attribute->value[$locale][$row["key"]] == 1 ? 'checked="checked"' : "" }}>
                <label class="onoffswitch-label" for="onoff-node-{{$attribute->id}}-{{$locale}}-{{$key}}">
                    <div class="onoffswitch-inner"></div>
                    <div class="onoffswitch-switch"></div>
                </label>
            </div>
        </div>
        <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
            <label>{{$row["name"]}}</label>
        </div>
    @endforeach
    <div class="clearfix"></div>
@endif

