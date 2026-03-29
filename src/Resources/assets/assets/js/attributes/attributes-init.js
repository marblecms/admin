;(function(global){

    for( var file in global.Attributes.getFiles() ){
        $(document.body).append('<script type="text/javascript" src="' + file + '"></script>');
    }
    
    Attributes.ready();

})(window);