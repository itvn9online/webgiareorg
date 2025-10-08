<?php
// for mobile
if ( wp_is_mobile() ) {
    echo do_shortcode( '[block id="header-mobile"]' );
}
// for pc
else {
    echo do_shortcode( '[block id="header"]' );
}