//console.log(typeof jQuery);
//
(function ($) {
    // tạo menu cho phần my account
    $('.json-to-menu').each(function () {
        var a = $.trim($(this).html());

        //
        a = JSON.parse(a);
        //console.log(a);

        //
        var str = '';
        if (typeof a.arr != 'undefined') {
            var arr = a.arr;
            //console.log(arr);
            for (var x in arr) {
                str += '<li><a href="' + a.link + x + '">' + arr[x] + '</a></li>';
            }
            str = '<ul class="sub-menu">' + str + '<ul>';
        }

        //
        str = '<li><a href="' + a.link + '">' + a.name + '</a>' + str + '</li>';

        //
        var cl = 'actived';
        if (typeof a.class != 'undefined') {
            cl += ' ' + a.class;
        }

        //
        $(this).html(str).addClass(cl);
    });
})(jQuery);

//
setInterval(function () {
    var new_scroll_top = window.scrollY || jQuery(window).scrollTop();

    //
    if (new_scroll_top > 120) {
        jQuery('body').addClass('ebfixed-top-menu');

        //
        //WGR_lazyload_footer_content();

        //
        if (new_scroll_top > 500) {
            //		if ( new_scroll_top < old_scroll_top ) {
            jQuery('body').addClass('ebshow-top-scroll');

            //
            //_global_js_eb.ebBgLazzyLoad(new_scroll_top);
        } else {
            jQuery('body').removeClass('ebshow-top-scroll');
        }
    } else {
        jQuery('body').removeClass('ebfixed-top-menu').removeClass('ebshow-top-scroll');
    }
}, 200);
