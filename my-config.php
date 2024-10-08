<?php

/**
 * Chức năng này sẽ tạo 1 config để kết nối db, dành cho cache kết nối và cache qua bảng memory của db
 * Create date: %date%
 */

/** The name of the database for WordPress */
//define('DB_MY_NAME', '%name%');

/** MySQL database username */
//define('DB_MY_USER', '%user%');

/** MySQL database password */
//define('DB_MY_PASSWORD', '%pass%');

/** MySQL hostname */
//define('DB_MY_HOST', '%host%');

//
define('REDIS_MY_HOST', '%redis_host%');
define('REDIS_MY_PORT', '%redis_port%');
defined('EB_REDIS_CACHE') || define('EB_REDIS_CACHE', enable_redis);
defined('EB_CACHE_PREFIX') || define('EB_CACHE_PREFIX', 'str_cache_prefix');
