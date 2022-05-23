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

/*
 * nạp top của webgiareorg (nếu có)
 */
if ( file_exists( WGR_CHILD_PATH . 'Views/footer.php' ) ) {
    ?>
<footer id="footer" class="footer-wrapper">
<?php
include WGR_CHILD_PATH . 'Views/footer.php';
?>
</header>
<?php
}
/*
 * không thì dùng top của flatsome
 */
else {
    ?>
<footer id="footer" class="footer-wrapper">
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
WGR_adds_js( [
    WGR_BASE_PATH . 'thirdparty/vuejs-2.6.10/vue.min.js',
    WGR_BASE_PATH . 'javascript/footer.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );

?>
<div id="oi_popup"></div>
</body>
</html>
<?php

/*
 * Bên trên là footer của flatsome
 */

// kết thúc website -> in ra cache nếu có
//require __DIR__ . '/footer_cache.php';
