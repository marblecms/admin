;(function(global){

    function ImageEditor(){

        this.$modal = null;
        this.$image = null;
        this.image = null;
        this.cropper = null;
        this.callback = $.noop;
        this.transformations = {};
    }

    ImageEditor.prototype.init = function(){

        this.$modal = $("#image-editor-modal");
        this.$image = this.$modal.find(".editor-image");

        this.registerEventHandlers();

    };

    ImageEditor.prototype.open = function(image){

        this.image = image;
        this.$modal.modal("show");

        this.initModal();

    };

    ImageEditor.prototype.registerEventHandlers = function(){

        this.$modal.on("click", ".save-image", function(){

            this.callback(this.transformations);

        }.bind(this));

        this.$modal.on("hidden.bs.modal", function(){
            this.cropper.destroy();
        }.bind(this));

    };

    ImageEditor.prototype.initModal = function(){

        this.$image.attr("src", this.image.filename);

        this.cropper = new Cropper(this.$image.get(0), {
            viewMode: 1,
            zoomOnWheel: false,
            built: function(){

                this.cropper.setData(this.image.transformations);

            }.bind(this),
            crop: function(e){

                this.transformations = this.cropper.getData();

            }.bind(this)
        });

        window.cropper = this.cropper;
    };

    ImageEditor.prototype.done = function(callback){

        this.callback = callback;

    };



    window.ImageEditor = new ImageEditor;

})(window);