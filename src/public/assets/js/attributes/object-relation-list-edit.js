;(function(global){
    
    function ObjectRelationList(containerId, inputName, attributeId, languageId){

        this.$container = $("#" + containerId);
        this.$view = this.$container.find(".attribute-object-relation-list-view");
        this.$inputs = this.$container.find(".attribute-object-relation-list-inputs");
        this.$add = this.$container.find(".attribute-object-relation-list-add");
        this.inputName = inputName;
        this.attributeId = attributeId;
        this.languageId = languageId;

        this.nodes = [];

        this.registerEventHandlers();
        this.renderView();


        this.$view.sortable({
            stop: function(){
                var $elements = this.$view.find(".object-relation-card"),
                    sortOrder = [],
                    sortedNodes = [];
                
                $elements.each(function(i, el){

                    var index = $(el).data("index");

                    sortOrder.push(index);
                    sortedNodes.push(this.nodes[index]);

                }.bind(this));

                this.nodes = sortedNodes;

                this.renderView();

                $.post("/admin/node/ajaxattribute/" + this.attributeId + "/" + this.languageId, {
                    method: "sort",
                    sortOrder: sortOrder
                });

            }.bind(this)
        });
    };

    ObjectRelationList.prototype.addNode = function(node){

        this.nodes.push(node);
        this.renderView();

    };

    ObjectRelationList.prototype.renderView = function(){

        this.$view.html("");
        this.$inputs.html("");
        console.log("rerender");

        for(var i in this.nodes){
            this.$view.append(
                '<div data-index="' + i + '" class="pull-left object-relation-card">'+
                    '<b class="nodename">' + this.nodes[i].name + '</b>' +
                    '<b class="delete" data-index="' + i + '">&times;</b>' +
                '</div>'
            );

            this.$inputs.append(
                '<input type="hidden" name="' + this.inputName + '" value="' + this.nodes[i].id + '" />'
            );
        }

        if(this.nodes.length === 0){
            this.$view.append(
                '<p>' +
                    'Keine Objekte Ausgewählt...' +
                '</p>'
            );
        }

    };

    ObjectRelationList.prototype.registerEventHandlers = function(){

        this.$container.on("click", ".delete", function(ev){

            this.removeNode($(ev.currentTarget).data("index"));

        }.bind(this));

        this.$add.click(function(){

            ObjectBrowser.open(function(node){

                this.addNode(node);

            }.bind(this));

        }.bind(this));

    };

    ObjectRelationList.prototype.removeNode = function(index){
        
        this.nodes.splice(index, 1);
        this.renderView();

    };
    
    global.Attributes.ObjectRelationList = ObjectRelationList;

})(window);