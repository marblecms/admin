$(function($) {
    // Restore tree collapsed state from localStorage
    var marbleTreeCollapsed = JSON.parse(localStorage.getItem('marble_tree_collapsed') || '[]');
    var marbleActiveAncestors = {};
    $('#sidebar-nav .active').each(function() {
        $(this).parents('li[data-node-id]').each(function() {
            marbleActiveAncestors[$(this).data('node-id')] = true;
        });
    });
    $('#sidebar-nav li[data-node-id]').each(function() {
        var $item = $(this);
        var nodeId = String($item.data('node-id'));
        if (marbleTreeCollapsed.indexOf(nodeId) !== -1 && !marbleActiveAncestors[nodeId]) {
            $item.removeClass('open');
            $item.children('.submenu').hide();
            var $img = $item.find('> a .tree-expand-icon');
            if ($img.length) $img.attr('src', $img.data('plus'));
        }
    });

    $('#sidebar-nav').on('click', '.dropdown-toggle .tree-expand-icon', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $img = $(this);
        var $item = $img.closest('li');

        $item.toggleClass('open');
        if ($item.hasClass('open')) {
            $item.children('.submenu').slideDown('fast');
            $img.attr('src', $img.data('minus'));
        } else {
            $item.children('.submenu').slideUp('fast');
            $img.attr('src', $img.data('plus'));
        }

        // Persist collapsed state
        var nodeId = String($item.data('node-id'));
        var collapsed = JSON.parse(localStorage.getItem('marble_tree_collapsed') || '[]');
        if ($item.hasClass('open')) {
            collapsed = collapsed.filter(function(id) { return id !== nodeId; });
        } else {
            if (collapsed.indexOf(nodeId) === -1) collapsed.push(nodeId);
        }
        localStorage.setItem('marble_tree_collapsed', JSON.stringify(collapsed));
    });
    $('body').on('mouseenter', '#page-wrapper.nav-small #sidebar-nav .dropdown-toggle', function(e) {
        var $sidebar = $(this).parents('#sidebar-nav');
        if ($(document).width() >= 992) {
            var $item = $(this).parent();
            $item.addClass('open');
            $item.children('.submenu').slideDown('fast');
        }
    });
    $('body').on('mouseleave', '#page-wrapper.nav-small #sidebar-nav > .nav-pills > li', function(e) {
        var $sidebar = $(this).parents('#sidebar-nav');
        if ($(document).width() >= 992) {
            var $item = $(this);
            if ($item.hasClass('open')) {
                $item.find('.open .submenu').slideUp('fast');
                $item.find('.open').removeClass('open');
                $item.children('.submenu').slideUp('fast');
            }
            $item.removeClass('open');
        }
    });
    $('#make-small-nav').click(function(e) {
        $('#page-wrapper').toggleClass('nav-small');
    });
    $(window).smartresize(function() {
        if ($(document).width() <= 991) {
            $('#page-wrapper').removeClass('nav-small');
        }
    });
    $('.mobile-search').click(function(e) {
        e.preventDefault();
        $('.mobile-search').addClass('active');
        $('.mobile-search form input.form-control').focus();
    });
    $(document).mouseup(function(e) {
        var container = $('.mobile-search');
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.removeClass('active');
        }
    });

    $("[data-toggle='tooltip']").each(function(index, el) {
        $(el).tooltip({
            placement: $(this).data("placement") || 'top'
        });
    });
});
$.fn.removeClassPrefix = function(prefix) {
    this.each(function(i, el) {
        var classes = el.className.split(" ").filter(function(c) {
            return c.lastIndexOf(prefix, 0) !== 0;
        });
        el.className = classes.join(" ");
    });
    return this;
};
(function($, sr) {
    window.debounce = function(func, threshold, execAsap) {
        var timeout;
        return function debounced() {
            var obj = this,
                args = arguments;

            function delayed() {
                if (!execAsap)
                    func.apply(obj, args);
                timeout = null;
            };
            if (timeout)
                clearTimeout(timeout);
            else if (execAsap)
                func.apply(obj, args);
            timeout = setTimeout(delayed, threshold || 100);
        };
    }
    jQuery.fn[sr] = function(fn) {
        return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr);
    };
})(jQuery, 'smartresize');


$("#search-field").keyup(debounce(function(){
    
    var $searchResults = $(".autocomplete-container .list-group"),
        query = $(this).val();
        
    if( query.length < 3 ){
        $searchResults.removeClass("visible");
        return;
    }
    
    $.get("/admin/item/search.json?q=" + encodeURIComponent(query), function(nodes){
        
        $searchResults.html("");
        
        for(var key in nodes){
            $searchResults.append('<li class="list-group-item"><a href="/admin/item/edit/' + nodes[key].id + '">' + nodes[key].name + '</a></li>');
        }
        
        $searchResults.addClass("visible");
        
    });
    
}));