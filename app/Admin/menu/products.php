<?php

// menu con
$base_url = get_site_url() . '/';

?>
<h1>Danh sách sản phẩm</h1>
<div>
    <a href="<?php echo $base_url; ?>wp-json/products/for-google" target="_blank"
        class="button button-primary button-large">for Google</a>
    <a href="<?php echo $base_url; ?>wp-json/products/for-facebook" target="_blank"
        class="button button-primary button-large">for Facebook</a>
</div>