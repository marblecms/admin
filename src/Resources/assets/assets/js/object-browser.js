;(function(global){

    function ObjectBrowser(){

        this.$modal = null;
        this.createdNode = null;
        this.callback = $.noop;

    }

    ObjectBrowser.prototype.init = function(){

        this.$addModal = $("#object-browser-modal-add");
        this.$createModal = $("#object-browser-modal-create");

        this.$addModal.on("click", ".object-browser-node", function(ev){

            var $el = $(ev.currentTarget),
                node = {
                    id: $el.data("node-id"),
                    name: $el.data("node-name")
                };
            
            this.nodeSelected(node);

            this.close();

        }.bind(this));
        

        this.$createModal.on("click", ".save-created-object", function(){
            
            this.callback(this.createdNode);
            
        }.bind(this));
        

    };
    
    ObjectBrowser.prototype.setNode = function(node){
        
        this.$createModal.find(".save-created-object").removeClass("disabled");
        this.createdNode = node;
        
    };

    ObjectBrowser.prototype.open = function(callback){

        this.callback = callback;
        this.$addModal.modal("show");

    };
    
    ObjectBrowser.prototype.create = function(callback){
        
        this.callback = callback;
        this.$createModal.modal("show");
        
        var $iframe = this.$createModal.find("iframe");
        $iframe.attr("src", $iframe.data("src"));
    };

    ObjectBrowser.prototype.nodeSelected = function(node){

        this.callback(node);
        this.callback = $.noop;

    };

    ObjectBrowser.prototype.close = function(){

        this.$addModal.modal("hide");
        this.$createModal.modal("hide");

    };


    window.ObjectBrowser = new ObjectBrowser;

})(window);