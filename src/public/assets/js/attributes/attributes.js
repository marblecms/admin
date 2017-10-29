;(function(global){

    global.JavascriptFiles = {};
    
    function Attributes() {
        this.files = {};
        this.callbacks = [];
    };
    
    Attributes.prototype.addFile = function(file){
        this.files[file] = true;
    };
    
    Attributes.prototype.getFiles = function(){
        return this.files;
    };
    
    Attributes.prototype.ready = function(callback){
        
        if( callback ){
            
            this.callbacks.push(callback);
            
        }else{
            
            this.callbacks.forEach(function(callback){
                callback();
            });
            
        }
        
    };
    
    global.Attributes = new Attributes;
    
})(window);