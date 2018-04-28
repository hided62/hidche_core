jQuery(function($){
    $.each(window.sammo_menu, function(idx){
        var href = this[0];
        var name = this[1];
        var target = (this.length > 2)?this[2]:null;

        var $a = $('<a class="nav-link" href="{0}">{1}</a>'.format(href, name));
        if(target){
            $a.attr('target', target);
        }
        var $li = $('<li class="nav-item"></li>').append($a);
        $('#navbarNav .navbar-nav').append($li);
        console.log(this);
    });
});