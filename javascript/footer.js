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
