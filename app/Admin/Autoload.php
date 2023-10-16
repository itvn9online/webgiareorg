<?php

//
//echo __FILE__ . ':' . __LINE__;

// chức năng này không chạy trong môi trường ajax
if (defined('DOING_AJAX') && DOING_AJAX) {
    //echo 'it\'s an Ajax call';
} else {
    //echo __FILE__ . '<br>' . PHP_EOL;

    // tự động nạp các file trong autoload
    foreach (glob(__DIR__ . '/autoload/*.php') as $filename) {
        //echo $filename . '<br>' . PHP_EOL;
        include $filename;
    }
}
