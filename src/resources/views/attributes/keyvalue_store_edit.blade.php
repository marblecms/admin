
    <div id="selectbox-config-{{$attribute->id}}-{{$locale}}" style="background: #f4f4f4;padding: 15px;border-radius: 3px">

        <div style="width: 120px; display:inline-block;">Schlüssel</div>
        <div style="width: 300px; display:inline-block;">Wert</div>
        <br />
        <div class="rows">
            @if($attribute->value[$locale])
                @foreach($attribute->value[$locale] as $key => $row)
                    <div style="padding-bottom: 3px">
                        <input type="text" name="attributes[{{$attribute->id}}][{{$locale}}][{{$key}}][key]" value="{{$row["key"]}}" class="form-control" style="display: inline-block; width:100px; margin-right:10px" />
                        <input type="text" name="attributes[{{$attribute->id}}][{{$locale}}][{{$key}}][value]" value="{{$row["value"]}}" class="form-control" style="display: inline-block; width:300px" />
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
                    '<input type="text" name="attributes[{{$attribute->id}}][{{$locale}}][' + i + '][key]" value="" class="form-control" style="display: inline-block; width:100px; margin-right:10px" /> ' +
                    '<input type="text" name="attributes[{{$attribute->id}}][{{$locale}}][' + i + '][value]" value="" class="form-control" style="display: inline-block; width:300px" /> ' +
                    '<a href="javascript:;" class="cancel remove-row">&times;</a>' +
                '</div>'
            );
        });
        
        container.find(".remove-row").on("click", function(){
            $(this).parent().remove();
        });
        
    })();
</script>