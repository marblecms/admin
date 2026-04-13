;(function(){
    $(".lang-switch").click(function(){
        var $parent = $(this).parent().parent(),
            lang = $(this).data("lang");

        $parent.find(".lang-switch, .lang-content").removeClass("active");

        $parent.find("[data-lang=" + lang + "]").addClass("active");
    });

    $(document).on("click", ".marble-group-tab", function(){
        var $container = $(this).closest(".marble-group-tab-container"),
            group = $(this).data("group");

        $container.find(".marble-group-tab, .marble-group-panel").removeClass("active");
        $container.find("[data-group=" + group + "]").addClass("active");
    });
})();