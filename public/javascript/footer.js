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
            str = '<ul class="sub-menu">' + str + '</ul>';
        }

        //
        str = '<li><a href="' + a.link + '">' + a.name + '</a>' + str + '</li>';

        //
        var cl = 'actived';
        if (typeof a.class != 'undefined') {
            cl += ' ' + a.class;
        }

        //
        $(this).html(str).removeClass('d-none').addClass(cl);
    });

    //
    jQuery('a[href="#"]').attr({
        'href': 'javascript:;',
        'rel': 'nofollow'
        //}).click(function () {
        //return false;
    });

    //
    jQuery('a').each(function () {
        // chỉnh lại link cho phone call
        var a = $(this).attr('href') || '';
        if (a.split('tel:').length > 1) {
            $(this).attr({
                href: 'tel:' + a.split('tel:')[1]
            });
        }
        if (a.split('mailto:').length > 1) {
            $(this).attr({
                href: 'mailto:' + a.split('mailto:')[1]
            });
        }

        //
        if ((jQuery(this).attr('aria-label') || '') == '') {
            $(this).attr({
                'aria-label': $(this).attr('title') || $(this).attr('data-title') || 'External'
            });
        }
    });

    ///
    $('a.ez-toc-link').each(function () {
        var a = $(this).attr('href') || '';
        if (a.substr(0, 1) == '#') {
            $(this).attr({
                href: eb_this_current_url + a
            });
        }
    });

    //
    $(document).ready(function () {
        $('body').addClass('document-ready');
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
