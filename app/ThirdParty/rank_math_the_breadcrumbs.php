<?php
if (function_exists('rank_math_the_breadcrumbs')) {
    add_action('flatsome_before_blog', 'rank_math_the_breadcrumbs');
}