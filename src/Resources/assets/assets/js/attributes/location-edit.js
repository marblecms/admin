;(function(global){

    function Location(containerId, attributeId, languageId){

        this.$container = $("#" + containerId);
        this.map = null;
        this.geocoder = null;
        this.callback = $.noop;
        this.attributeId = attributeId;
        this.languageId = languageId;
        this.locations = [];
        
        Location.registry.push(this);
        
        this.registerEventHandlers();

    };
    
    Location.prototype.ready = function(callback){
        
        this.callback = callback;
        
    };
    
    Location.prototype.init = function(){
        
        this.map = new google.maps.Map(this.$container.find(".google-map").get(0), {
            zoom: 8
        });
        
        this.geocoder = new google.maps.Geocoder();
        
        this.callback();
        
    };
    
    Location.prototype.redrawMap = function(){
        
        google.maps.event.trigger(this.map, 'resize');
        this.renderView();
        
    };
    
    Location.prototype.renderView = function(){
        
        var $items = this.$container.find(".items");
        
        this.$container.find(".items").html("");
        
        for(var key in this.locations){
            ;(function(key){
                
                var autocomplete;
                
                $items.append(
                    '<div class="row" style="margin-bottom: 10px" data-index="' + key + '">' +
                        '<div class="col-md-4">' + 
                            '<input type="text" name="attributes[' + this.attributeId +'][' + this.languageId + '][' + key + '][name]" value="' + this.locations[key].name + '" data-field="name" class="form-control" /> ' +
                        '</div>' +
                        '<div class="col-md-7">' + 
                            '<input type="text" name="attributes[' + this.attributeId +'][' + this.languageId + '][' + key + '][address]" value="' + this.locations[key].address + '" data-field="address" class="form-control" /> ' +
                        '</div>' +
                        '<div class="col-md-1 text-right">' + 
                            '<input type="hidden" name="attributes[' + this.attributeId +'][' + this.languageId + '][' + key + '][lat]" value="' + this.locations[key].lat + '" /> ' +
                            '<input type="hidden" name="attributes[' + this.attributeId +'][' + this.languageId + '][' + key + '][lon]" value="' + this.locations[key].lon + '" /> ' +
                            '<a href="javascript:;" class="btn btn-danger btn-xs delete">&times;</a>' +
                        '</div>' +
                    '</div>'
                );
                
                autocomplete = new google.maps.places.Autocomplete(
                    $items.find("> div").last().find("[data-field=address]").get(0),
                    {
                        types: ['geocode']
                    }
                );
            
                autocomplete.addListener("place_changed", function(){
                    
                    this.locations[key].lat = autocomplete.getPlace().geometry.location.lat();
                    this.locations[key].lon = autocomplete.getPlace().geometry.location.lng();
                    this.locations[key].address = autocomplete.getPlace().formatted_address;
                    
                    if( this.locations[key].marker ){
                        
                        var latLon = new google.maps.LatLng( this.locations[key].lat, this.locations[key].lon );
                        
                        this.locations[key].marker.setPosition( latLon );
                    }else{
                        this.locations[key].marker = new google.maps.Marker({
                            map: this.map,
                            position: {
                                lat: this.locations[key].lat,
                                lng: this.locations[key].lon
                            }
                        });
                    }
                    
                    this.renderView();
                    
                }.bind(this));
                    
            }.bind(this))(key);
        }
        

        this.panMap();
        
    };
    
    Location.prototype.registerEventHandlers = function(){

        this.$container.on("click", ".delete", function(ev){
            
            var index = $(ev.currentTarget).parent().parent().data("index");
            
            this.removeLocation(index);

        }.bind(this));
        
        this.$container.on("keyup", ".form-control", function(ev){
            
            var $el = $(ev.currentTarget),
                index = $el.parent().parent().data("index");
            
            this.locations[index][$el.data("field")] = $el.val();
            
        }.bind(this));
        
        this.$container.find(".add-location").click(function(){
            
            this.addMarker({
                name: "",
                address: "",
                lat: 0,
                lon: 0
            });

        }.bind(this));

    };

    Location.prototype.removeLocation = function(index){
        
        if( this.locations[index].marker ){
            this.locations[index].marker.setMap(null);
        }
        
        this.locations.splice(index, 1);
        this.renderView();

    };
    
    Location.prototype.panMap = function(){
        
        var bounds = new google.maps.LatLngBounds();
        
        for( var key in this.locations ){
            if( this.locations[key].marker ){
                bounds.extend(this.locations[key].marker.getPosition());
            }
        }
        
        this.map.fitBounds(bounds);
        this.map.setCenter(bounds.getCenter());
        
    };
    
    Location.prototype.addMarker = function(location){
        
        var options = {
            "address": location.address
        };
        
        this.geocoder.geocode(options, function(results, status){
            
            if( status == google.maps.GeocoderStatus.OK ){
                
                this.map.setCenter(results[0].geometry.location);
                
                var marker = new google.maps.Marker({
                    map: this.map,
                    position: results[0].geometry.location
                });
                
                location.marker = marker;
                
            }
    
            this.locations.push(location);
            this.renderView();
            
        }.bind(this));
        
    };
    
    window.initMap = function(){
        
        Location.registry.forEach(function(instance){
            
            instance.init();
            
        });
        
    };
    
    Location.registry = [];

    global.Attributes.Location = Location;

})(window);