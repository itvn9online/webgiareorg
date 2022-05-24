<?php

// khi cần chuyển các file tĩnh sang url khác để giảm tải cho server chính thì dùng chức năng này
defined( 'CDN_BASE_URL' ) || define( 'CDN_BASE_URL', '' );

// thuộc tính này để xác định code áp dụng cho plugin wocomerce -> sẽ có 1 số tính năng bổ sung cho nó
define( 'WGR_FOR_WOOCOMERCE', class_exists( 'WooCommerce' ) ? true : false );