;(function(global){

    function Image(containerId, attributeId, languageId){

        this.$container = $("#" + containerId);
        this.$view = this.$container.find(".attribute-image-view");
        this.$input = this.$container.find(".attribute-image-input");
        this.image = null;
        this.attributeId = attributeId;
        this.languageId = languageId;

        this.registerEventHandlers();
        this.renderView();

    };

    Image.prototype.setImage = function(image){

        this.image = image;
        this.renderView();

    };

    Image.prototype.renderView = function(){

        this.$view.html("");

        if( this.image ){
            this.$view.append(
                '<div class="pull-left image-card">'+
                    '<img src="' + this.image.thumbnailFilename + '" />' +
                    '<b class="filename">' + this.image.originalFilename + '</b>' +
                    '<b class="delete">&times;</b>' +
                '</div>'
            );
        }else{
            this.$view.append(
                '<p>' +
                    'Kein Bild Ausgewählt...' +
                '</p>'
            );
        }

    };

    Image.prototype.registerEventHandlers = function(){

        this.$container.on("click", ".delete", function(ev){

            this.removeImage();

        }.bind(this));

        this.$container.on("click", "img", function(ev){

            this.editImage();

        }.bind(this));

    };

    Image.prototype.editImage = function(){

        ImageEditor.done(function(transformations){

            this.image.transformations = transformations;

            $.post("/admin/node/ajaxattribute/" + this.attributeId + "/" + this.languageId, {
                method: "saveTransformations",
                data: this.image.transformations
            });

        }.bind(this));
        
        ImageEditor.open(this.image);

    };

    Image.prototype.removeImage = function(){
        this.image = null;
        this.$input.val("");
        this.renderView();
    };

    global.Attributes.Image = Image;

})(window);