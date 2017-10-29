<div id="attribute-location-{{$attribute->id}}-{{$locale}}" class="google-map">
    <div class="google-map"></div>
    
    <div class="attribute-container">
        
        <div class="row" style="margin-bottom: 10px">
            <div class="col-md-4">
                Name
            </div>
            <div class="col-md-8">
                Adresse
            </div>
        </div>
        
        <div class="items"></div>
        
        <a href="javascript:;" class="add-location btn btn-info btn-xs">hinzufügen</a>
    </div>
</div>
<script>
    Attributes.ready(function(){

        var container = new Attributes.Location("attribute-location-{{$attribute->id}}-{{$locale}}", {{$attribute->id}}, "{{$locale}}");

        @if($attribute->value[$locale])
            container.ready(function(){
                
                @foreach($attribute->value[$locale] as $key => $row)
                    container.addMarker({
                        address: "{!! $row['address'] !!}",
                        name: "{{$row['name']}}",
                        lat: "{{$row['lat']}}",
                        lon: "{{$row['lon']}}",
                    });
                @endforeach
            
            });
        @endif
        
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            container.redrawMap();
        });

    });
</script>

