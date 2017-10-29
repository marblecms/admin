<div class="row">
    <div class="col-md-6">
        @include("admin::node.edit_attribute", array("attribute" => $attributes->location_a))
    </div>
    <div class="col-md-6">
        @include("admin::node.edit_attribute", array("attribute" => $attributes->location_b))        
    </div>
</div>