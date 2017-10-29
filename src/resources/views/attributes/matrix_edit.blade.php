
    <div id="selectbox-config-{{$attribute->id}}-{{$locale}}" style="background: #f4f4f4;padding: 15px;border-radius: 3px">


        @foreach($attribute->classAttribute->configuration as $column)
            <div style="width: {{$column["width"] + 10}}px; display:inline-block;">{{$column["value"]}}</div>
        @endforeach
        <br />
        <div class="rows">
            @if($attribute->value[$locale] and $attribute->classAttribute->configuration)
                @foreach($attribute->value[$locale] as $key => $attributeValue)
                    <div style="padding-bottom: 3px">
                        @foreach($attribute->classAttribute->configuration as $column)
                            <input type="text" name="attributes[{{$attribute->id}}][{{$locale}}][{{$key}}][{{$column["key"]}}]" value="{{ isset($attributeValue[$column["key"]]) ? $attributeValue[ $column["key"] ] : "" }}" class="form-control" style="display: inline-block; width:{{$column["width"]}}px; margin-right:10px" />
                        @endforeach
                        <a href="javascript:;" class="cancel remove-row">&times;</a>
                    </div>
                @endforeach
            @endif
        </div>
    
        <br />
        <a href="javascript:;" class="add-row btn btn-info btn-xs">neue zeile</a>
    </div>
<script>
    ;(function(){
        
        var container = $("#selectbox-config-{{$attribute->id}}-{{$locale}}"),
            i = container.find(".rows > div").length - 1;

        container.find(".add-row").click(function(){
            
            i++;
            
            container.find(".rows").append(
                '<div style="padding-bottom: 3px">' +
                    @foreach($attribute->classAttribute->configuration as $column)
                        '<input type="text" name="attributes[{{$attribute->id}}][{{$locale}}][' + i + '][{{$column["key"]}}]" value="" class="form-control" style="display: inline-block; width:{{$column["width"]}}px; margin-right:10px" /> ' +
                    @endforeach
                    '<a href="javascript:;" class="cancel remove-row">&times;</a>' +
                '</div>'
            );
        });
        
        container.find(".remove-row").on("click", function(){
            $(this).parent().remove();
        });
        
    })();
</script>