@include("admin::node.edit_attribute", array("attribute" => $attributes->name))

@include("admin::node.edit_attribute", array("attribute" => $attributes->slug))

<div class="row">
    <div class="col-md-6">
        @include("admin::node.edit_attribute", array("attribute" => $attributes->layout))
    </div>
    <div class="col-md-6">
        @include("admin::node.edit_attribute", array("attribute" => $attributes->status))        
    </div>
</div>