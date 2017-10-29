;(function(){
    $(".lang-switch").click(function(){
        var $parent = $(this).parent().parent(),
            lang = $(this).data("lang");
        
        $parent.find(".lang-switch, .lang-content").removeClass("active");
            
        $parent.find("[data-lang=" + lang + "]").addClass("active");
    });
})();