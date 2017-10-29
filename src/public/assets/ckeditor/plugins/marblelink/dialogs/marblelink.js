CKEDITOR.dialog.add( 'marblelink', function( editor ) {
    return {
        title: 'CMS Link Properties',
        minWidth: 400,
        minHeight: 200,

        contents: [
            {
                id: 'tab-basic',
                label: 'Basic Settings',
                elements: [
                    {
                        type: 'text',
                        id: 'node',
                        label: 'Node',
                        validate: CKEDITOR.dialog.validate.notEmpty( "Node cannot be empty." ),
                        $searchResults: null,
                        selectedNode: null,

                        setup: function( element ) {
                            var $parent = $(this.getInputElement().$.parentNode);
                            
                            this.$searchResults = $('<div class="ck-list-group" />');
                            $parent.append(this.$searchResults);
                        },
                        
                        onKeyUp: debounce(function(e){

                            var query = e.sender.getValue();
    
                            if( query.length < 1 ){
                                this.$searchResults.removeClass("visible");
                                return;
                            }

                            $.get("/admin/node/search.json?q=" + encodeURIComponent(query), function(nodes){
                                
                                this.$searchResults.html("");
    
                                for(var key in nodes){
                                    
                                    (function(context, node, $el){
                                        $el = $('<div class="list-group-item">' + node.name + '</div>');
                                    
                                        $el.on("click", function(){
                                            context.onSelectedNode(node);
                                        });
                                    
                                        context.$searchResults.append($el);
                                    })(this, nodes[key]);
                                    
                                }
    
                                this.$searchResults.addClass("visible");
    
                            }.bind(this));
                        }),
                        
                        onSelectedNode: function(node){
                            
                            this.selectedNode = node;
                            
                            this.$searchResults.html("");
                            
                            this.setValue(node.name);
                        },

                        commit: function( element ) {
                            element.setAttribute( "href", '{% node-link:' + this.selectedNode.id + ' %}' );
                        }
                    }
                ]
            }
        ],

        onShow: function() {
            var selection = editor.getSelection();
            var element = selection.getStartElement();
            var text = selection.getSelectedText();
            
            if ( element )
                element = element.getAscendant( 'a', true );

            if ( !element || element.getName() != 'a' ) {
                element = editor.document.createElement( 'a' );
                this.insertMode = true;
            }
            else
                this.insertMode = false;
            
            element.setText(text);
            
            this.element = element;
            
            this.setupContent( this.element );
        },

        onOk: function() {
            var dialog = this;
            var abbr = this.element;
            this.commitContent( abbr );
            
            if ( this.insertMode )
                editor.insertElement( abbr );
        }
    };
});