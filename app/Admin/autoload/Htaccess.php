<?php

//
$root_htaccess = ABSPATH . '.htaccess';
//echo $root_htaccess . '<br>' . PHP_EOL;
if (!file_exists($root_htaccess) || strpos(file_get_contents($root_htaccess), 'RewriteCond %{HTTPS} off') === false) {
    echo $root_htaccess . '<br>' . PHP_EOL;

    //
    $update_htaccess = file_get_contents('aaaaaa');
}
