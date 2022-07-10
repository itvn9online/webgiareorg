<?php

/*
 * Do đoạn code viewport của flatsome chưa đạt chuẩn điểm của google page speed -> cần phải chỉnh lại
 */
// Remove Header Viewport Meta 
function remove_flatsome_viewport_meta() {
    remove_action( 'wp_head', 'flatsome_viewport_meta', 1 );
}
add_action( 'init', 'remove_flatsome_viewport_meta', 15 );