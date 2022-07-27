// JavaScript Document
(function ($) {
    //
    $('.click-toggle-mobile-menu').click(function () {
        $('body').toggleClass('active-mobile-menu');
    });

    // tạo hiệu ứng mở menu cho bản mobile
    if ($(window).width() < 768) {
        $('.header-mobile-menu .sub-menu').parent('li').prepend('<button class="toggle"><i class="icon-angle-down"></i></button>');
    }
})(jQuery);
