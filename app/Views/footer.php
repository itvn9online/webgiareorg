<?php

/**
 * The template for displaying the footer.
 *
 * @package flatsome
 * @daidq - 0984533228 - itvn9online@gmail.com
 * Chỉnh sửa và phát triển theo hướng chuyên cho thị trường Việt Nam
 */

global $flatsome_opt;
?>
</main>
<?php

/**
 * nạp top của webgiareorg (nếu có)
 */
if (is_file(WGR_CHILD_PATH . 'Views/footer.php')) {
?>
    <footer id="footer" class="footer-wrapper wgr-primary-footer">
        <?php
        include WGR_CHILD_PATH . 'Views/footer.php';
        ?>
    </footer>
    <div onClick="window.scroll(0, 0);" class="back-to-top button icon invert plain fixed bottom z-1 is-outline circle"><i class="icon-angle-up"></i></div>
<?php
} else {
    /**
     * không thì dùng top của flatsome
     */
?>
    <footer id="footer" class="footer-wrapper flatsome-primary-footer">
        <?php do_action('flatsome_footer'); ?>
    </footer>
<?php
}

?>
</div>
<!-- <div id="fb-root"></div> -->
<?php

//
//global $__cf_row;
//echo $__cf_row[ 'cf_js_allpage' ];

//
wp_footer();

//
//require __DIR__ . '/footer_cache_quick_cart.php';

// các file js có thể để chế độ defer
WGR_adds_js([
    WGR_BASE_PATH . 'public/thirdparty/vuejs/vue.min.js',
    //WGR_BASE_PATH . 'public/thirdparty/vuejs/vue' . ( WP_DEBUG === true ? '.min' : '' ) . '.js',
    WGR_BASE_PATH . 'public/javascript/footer.js',
    WGR_CHILD_PATH . 'javascript/d.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

?>
<div id="oi_popup"></div>
</body>

</html>
<?php

/**
 * Bên trên là footer của flatsome
 */

// kết thúc website -> in ra cache nếu có
include __DIR__ . '/footer_cache.php';
