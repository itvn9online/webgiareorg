<?php

/**
 * Sử dụng breadcrumbs của Rank Math SEO
 */
if (function_exists('rank_math_the_breadcrumbs')) {
    function WGR_rank_math_the_breadcrumbs()
    {
        ob_start();
        rank_math_the_breadcrumbs();
        $result = ob_get_contents();
        ob_end_clean();

        //
        echo '<div class="container">' . $result . '</div>';
    }
    add_action('flatsome_before_blog', 'WGR_rank_math_the_breadcrumbs');
}
