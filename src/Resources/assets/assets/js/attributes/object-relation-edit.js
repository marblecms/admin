;(function(global){
    
    function ObjectRelation(containerId){

        this.$container = $("#" + containerId);
        this.$view = this.$container.find(".attribute-object-relation-view");
        this.$input = this.$container.find(".attribute-object-relation-input");
        this.$add = this.$container.find(".attribute-object-relation-add");
        this.$create = this.$container.find(".attribute-object-relation-create");

        this.node = null;
        this.selectedCallback = $.noop;

        this.registerEventHandlers();
        this.renderView();

    };

    ObjectRelation.prototype.setNode = function(node){

        this.node = node;
        this.renderView();
        this.$input.val(this.node.id);

    };
    
    ObjectRelation.prototype.selected = function(callback){
        this.selectedCallback = callback;
    };

    ObjectRelation.prototype.renderView = function(){

        this.$view.html("");

        if( this.node ){
            this.$view.append(
                '<div class="pull-left object-relation-card">'+
                    '<b class="nodename">' + this.node.name + '</b>' +
                    '<b class="delete">&times;</b>' +
                '</div>'
            );
        }else{
            this.$view.append(
                '<p>' +
                    'Kein Objekt Ausgewählt...' +
                '</p>'
            );
        }

    };

    ObjectRelation.prototype.registerEventHandlers = function(){

        this.$container.on("click", ".delete", function(ev){

            this.removeNode();

        }.bind(this));

        this.$add.click(function(){
            
            ObjectBrowser.open(function(node){

                this.setNode(node);
                
                this.selectedCallback(node);

            }.bind(this));

        }.bind(this));
        
        this.$create.click(function(){
            
            ObjectBrowser.create(function(node){
                
                this.setNode(node);
                
            }.bind(this));
            
        }.bind(this));

    };
    
    ObjectRelation.prototype.removeNode = function(){

        this.node = null;
        this.$input.val("");
        this.renderView();

    };
    
    global.Attributes.ObjectRelation = ObjectRelation;

})(window);