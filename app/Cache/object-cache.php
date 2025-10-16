<?php

/**
 * Object Cache using Redis for WordPress
 * Compatible with WordPress Object Cache API
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WGR Object Cache Class
 */
class WGR_Object_Cache
{
    private $redis;
    private $cache = [];
    private $non_persistent_groups = [];
    private $multisite;
    private $blog_prefix;
    private $cache_hits = 0;
    private $cache_misses = 0;
    private $redis_connected = false;
    // một số cache wordpress để vĩnh viễn, mình sẽ cố định lại chứ để lâu sẽ khá tốn ram
    private $default_expire = 3600;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $blog_id, $table_prefix;

        // Set multisite and blog prefix
        $this->multisite = is_multisite();
        $this->blog_prefix = $this->multisite ? $blog_id : $table_prefix;

        // Connect to Redis
        $this->redis_connect();

        // Set non-persistent groups
        $this->non_persistent_groups = [
            'comment',
            'counts',
            'plugins',
            'themes',
        ];
    }

    /**
     * Connect to Redis
     */
    private function redis_connect()
    {
        if ($this->redis_connected) {
            return true;
        }

        // đảm bảo các thông số này phải được thiết lập ở wp-config thì mới kích hoạt chức năng
        if (!defined('WGR_REDIS_CACHE') || WGR_REDIS_CACHE !== true || !defined('WGR_CACHE_PREFIX') || empty(WGR_CACHE_PREFIX)) {
            $this->redis_connected = false;
            return false;
        }

        try {
            if (!class_exists('Redis')) {
                return false;
            }

            $this->redis = new Redis();

            // Get Redis config
            $host = defined('WGR_REDIS_HOST') ? WGR_REDIS_HOST : '127.0.0.1';
            $port = defined('WGR_REDIS_PORT') ? WGR_REDIS_PORT : 6379;
            $timeout = defined('WGR_REDIS_TIMEOUT') ? WGR_REDIS_TIMEOUT : 1;
            $password = defined('WGR_REDIS_PASSWORD') ? WGR_REDIS_PASSWORD : null;
            $database = defined('WGR_REDIS_DATABASE') ? WGR_REDIS_DATABASE : 0;

            // Connect
            $connected = $this->redis->connect($host, $port, $timeout);

            if (!$connected) {
                return false;
            }

            // Authenticate if password is set
            if ($password) {
                $this->redis->auth($password);
            }

            // Select database
            if ($database) {
                $this->redis->select($database);
            }

            $this->redis_connected = true;
            return true;
        } catch (Exception $e) {
            $this->redis_connected = false;
            return false;
        }
    }

    /**
     * Get cache key
     */
    private function get_cache_key($key, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        // Get cache prefix
        $cache_prefix = WGR_CACHE_PREFIX;

        return "{$cache_prefix}:{$this->blog_prefix}:{$group}:{$key}";
    }

    /**
     * Add data to cache
     */
    public function add($key, $data, $group = 'default', $expire = 0)
    {
        if (wp_suspend_cache_addition()) {
            return false;
        }

        if (empty($group)) {
            $group = 'default';
        }

        // Check if already exists
        if ($this->_exists($key, $group)) {
            return false;
        }

        return $this->set($key, $data, $group, $expire > 0 ? $expire : $this->default_expire);
    }

    /**
     * Set data to cache
     */
    public function set($key, $data, $group = 'default', $expire = 0)
    {
        if (empty($group)) {
            $group = 'default';
        }

        // Check if group is non-persistent
        if (in_array($group, $this->non_persistent_groups)) {
            $this->cache[$group][$key] = $data;
            return true;
        }

        // Store in memory cache
        $this->cache[$group][$key] = $data;

        // Store in Redis if connected
        if ($this->redis_connected) {
            $cache_key = $this->get_cache_key($key, $group);
            $serialized_data = maybe_serialize($data);

            try {
                return $this->redis->setex($cache_key, $expire > 0 ? $expire : $this->default_expire, $serialized_data);
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get data from cache
     */
    public function get($key, $group = 'default', $force = false, &$found = null)
    {
        if (empty($group)) {
            $group = 'default';
        }

        // Check memory cache first
        if (!$force && isset($this->cache[$group][$key])) {
            $found = true;
            $this->cache_hits++;
            return $this->cache[$group][$key];
        }

        // Check Redis if connected
        if ($this->redis_connected && !in_array($group, $this->non_persistent_groups)) {
            $cache_key = $this->get_cache_key($key, $group);

            try {
                $data = $this->redis->get($cache_key);

                if ($data === false) {
                    $found = false;
                    $this->cache_misses++;
                    return false;
                }

                $found = true;
                $this->cache_hits++;

                // Unserialize and store in memory
                $data = maybe_unserialize($data);
                $this->cache[$group][$key] = $data;

                return $data;
            } catch (Exception $e) {
                $found = false;
                $this->cache_misses++;
                return false;
            }
        }

        $found = false;
        $this->cache_misses++;
        return false;
    }

    /**
     * Delete data from cache
     */
    public function delete($key, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        // Remove from memory cache
        unset($this->cache[$group][$key]);

        // Remove from Redis if connected
        if ($this->redis_connected && !in_array($group, $this->non_persistent_groups)) {
            $cache_key = $this->get_cache_key($key, $group);

            try {
                return $this->redis->del($cache_key) > 0;
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if key exists
     */
    private function _exists($key, $group = 'default')
    {
        if (isset($this->cache[$group][$key])) {
            return true;
        }

        if ($this->redis_connected && !in_array($group, $this->non_persistent_groups)) {
            $cache_key = $this->get_cache_key($key, $group);

            try {
                return $this->redis->exists($cache_key);
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Flush all cache
     */
    public function flush()
    {
        $this->cache = [];

        if ($this->redis_connected) {
            try {
                // Only flush keys with our prefix
                $cache_prefix = WGR_CACHE_PREFIX;
                $pattern = "{$cache_prefix}:{$this->blog_prefix}:*";

                $keys = $this->redis->keys($pattern);
                if (!empty($keys)) {
                    $this->redis->del($keys);
                }

                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Replace cache data
     */
    public function replace($key, $data, $group = 'default', $expire = 0)
    {
        if (empty($group)) {
            $group = 'default';
        }

        if (!$this->_exists($key, $group)) {
            return false;
        }

        return $this->set($key, $data, $group, $expire > 0 ? $expire : $this->default_expire);
    }

    /**
     * Increment numeric cache item
     */
    public function incr($key, $offset = 1, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        if ($this->redis_connected && !in_array($group, $this->non_persistent_groups)) {
            $cache_key = $this->get_cache_key($key, $group);

            try {
                $value = $this->redis->incrBy($cache_key, $offset);
                $this->cache[$group][$key] = $value;
                return $value;
            } catch (Exception $e) {
                // Fall back to memory cache
            }
        }

        // Memory cache fallback
        $value = $this->get($key, $group);
        if ($value === false) {
            $value = 0;
        }

        $value += $offset;
        $this->set($key, $value, $group);

        return $value;
    }

    /**
     * Decrement numeric cache item
     */
    public function decr($key, $offset = 1, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        if ($this->redis_connected && !in_array($group, $this->non_persistent_groups)) {
            $cache_key = $this->get_cache_key($key, $group);

            try {
                $value = $this->redis->decrBy($cache_key, $offset);
                $this->cache[$group][$key] = $value;
                return $value;
            } catch (Exception $e) {
                // Fall back to memory cache
            }
        }

        // Memory cache fallback
        $value = $this->get($key, $group);
        if ($value === false) {
            $value = 0;
        }

        $value -= $offset;
        $this->set($key, $value, $group);

        return $value;
    }

    /**
     * Add groups to non-persistent list
     */
    public function add_non_persistent_groups($groups)
    {
        $groups = (array) $groups;
        $this->non_persistent_groups = array_unique(array_merge($this->non_persistent_groups, $groups));
    }

    /**
     * Switch blog prefix (for multisite)
     */
    public function switch_to_blog($blog_id)
    {
        $this->blog_prefix = $this->multisite ? $blog_id : $this->blog_prefix;
        $this->cache = [];
    }

    /**
     * Get cache statistics
     */
    public function stats()
    {
        echo "<p><strong>Cache Hits:</strong> {$this->cache_hits}</p>";
        echo "<p><strong>Cache Misses:</strong> {$this->cache_misses}</p>";

        if ($this->redis_connected) {
            try {
                $info = $this->redis->info();
                echo "<p><strong>Redis Connected:</strong> Yes</p>";
                echo "<p><strong>Redis Version:</strong> " . ($info['redis_version'] ?? 'Unknown') . "</p>";
                echo "<p><strong>Used Memory:</strong> " . ($info['used_memory_human'] ?? 'Unknown') . "</p>";
            } catch (Exception $e) {
                echo "<p><strong>Redis Connected:</strong> Error</p>";
            }
        } else {
            echo "<p><strong>Redis Connected:</strong> No</p>";
        }
    }

    /**
     * Close Redis connection
     */
    public function close()
    {
        if ($this->redis_connected && $this->redis) {
            try {
                $this->redis->close();
            } catch (Exception $e) {
                // Ignore errors on close
            }
        }
    }

    /**
     * Alias for close()
     */
    public function __destruct()
    {
        // Connection will be closed automatically
    }

    /**
     * Purge cache by pattern
     */
    public function purge_by_pattern($pattern)
    {
        if (!$this->redis_connected) {
            return false;
        }

        try {
            $cache_prefix = WGR_CACHE_PREFIX;
            $full_pattern = "{$cache_prefix}:{$this->blog_prefix}:{$pattern}";

            $keys = $this->redis->keys($full_pattern);
            if (!empty($keys)) {
                $deleted = $this->redis->del($keys);

                // Also clear from memory cache
                foreach ($keys as $key) {
                    $parts = explode(':', $key);
                    if (count($parts) >= 4) {
                        $group = $parts[2];
                        $cache_key = implode(':', array_slice($parts, 3));
                        unset($this->cache[$group][$cache_key]);
                    }
                }

                return $deleted;
            }

            return 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Purge cache by group
     */
    public function purge_group($group)
    {
        // Clear memory cache
        unset($this->cache[$group]);

        // Clear Redis cache
        return $this->purge_by_pattern("{$group}:*");
    }

    /**
     * Purge multiple groups
     */
    public function purge_groups($groups)
    {
        $total_deleted = 0;
        foreach ((array) $groups as $group) {
            $deleted = $this->purge_group($group);
            if ($deleted !== false) {
                $total_deleted += $deleted;
            }
        }
        return $total_deleted;
    }
}

// Initialize global cache object
$wp_object_cache = new WGR_Object_Cache();

/**
 * Auto-purge cache hooks
 */

// Purge post-related cache when post is updated/deleted
add_action('save_post', 'wgr_purge_post_cache', 10, 1);
add_action('delete_post', 'wgr_purge_post_cache', 10, 1);
add_action('trashed_post', 'wgr_purge_post_cache', 10, 1);
add_action('untrashed_post', 'wgr_purge_post_cache', 10, 1);

function wgr_purge_post_cache($post_id)
{
    global $wp_object_cache;

    if (!$wp_object_cache || !method_exists($wp_object_cache, 'purge_groups')) {
        return;
    }

    // Purge post-related cache groups
    $groups_to_purge = [
        'posts',
        'post_meta',
        'post_tag_relationships',
        'category_relationships',
        'post_format_relationships',
    ];

    $wp_object_cache->purge_groups($groups_to_purge);

    // Also purge specific post cache
    wp_cache_delete($post_id, 'posts');
    wp_cache_delete($post_id, 'post_meta');
}

// Purge term-related cache when term is updated/deleted
add_action('create_term', 'wgr_purge_term_cache', 10, 3);
add_action('edit_term', 'wgr_purge_term_cache', 10, 3);
add_action('delete_term', 'wgr_purge_term_cache', 10, 3);
add_action('set_object_terms', 'wgr_purge_term_relationships_cache', 10, 4);

function wgr_purge_term_cache($term_id, $tt_id = null, $taxonomy = null)
{
    global $wp_object_cache;

    if (!$wp_object_cache || !method_exists($wp_object_cache, 'purge_groups')) {
        return;
    }

    // Purge term-related cache groups
    $groups_to_purge = [
        'terms',
        'term_meta',
        'post_tag_relationships',
        'category_relationships',
        'post_format_relationships',
    ];

    $wp_object_cache->purge_groups($groups_to_purge);

    // Also purge specific term cache
    wp_cache_delete($term_id, 'terms');
    wp_cache_delete($term_id, 'term_meta');
}

function wgr_purge_term_relationships_cache($object_id, $terms, $tt_ids, $taxonomy)
{
    global $wp_object_cache;

    if (!$wp_object_cache || !method_exists($wp_object_cache, 'purge_groups')) {
        return;
    }

    // Purge relationship cache
    $groups_to_purge = [
        'post_tag_relationships',
        'category_relationships',
        'post_format_relationships',
    ];

    $wp_object_cache->purge_groups($groups_to_purge);
}

// Purge comment cache when comment is updated/deleted
add_action('comment_post', 'wgr_purge_comment_cache', 10, 1);
add_action('edit_comment', 'wgr_purge_comment_cache', 10, 1);
add_action('delete_comment', 'wgr_purge_comment_cache', 10, 1);
add_action('wp_set_comment_status', 'wgr_purge_comment_cache', 10, 1);

function wgr_purge_comment_cache($comment_id)
{
    global $wp_object_cache;

    if (!$wp_object_cache || !method_exists($wp_object_cache, 'purge_group')) {
        return;
    }

    // Purge comment cache
    $wp_object_cache->purge_group('comment');
}

// Purge user cache when user is updated/deleted
add_action('profile_update', 'wgr_purge_user_cache', 10, 1);
add_action('delete_user', 'wgr_purge_user_cache', 10, 1);
add_action('user_register', 'wgr_purge_user_cache', 10, 1);

function wgr_purge_user_cache($user_id)
{
    global $wp_object_cache;

    if (!$wp_object_cache || !method_exists($wp_object_cache, 'purge_group')) {
        return;
    }

    // Purge user-related cache
    $groups_to_purge = [
        'users',
        'usermeta',
        'user_meta',
    ];

    $wp_object_cache->purge_groups($groups_to_purge);

    // Also purge specific user cache
    wp_cache_delete($user_id, 'users');
    wp_cache_delete($user_id, 'usermeta');
}

// Purge option cache when option is updated
add_action('updated_option', 'wgr_purge_option_cache', 10, 1);
add_action('added_option', 'wgr_purge_option_cache', 10, 1);
add_action('deleted_option', 'wgr_purge_option_cache', 10, 1);

function wgr_purge_option_cache($option)
{
    global $wp_object_cache;

    if (!$wp_object_cache) {
        return;
    }

    // Delete specific option from cache
    wp_cache_delete($option, 'options');
    wp_cache_delete('alloptions', 'options');
    wp_cache_delete('notoptions', 'options');
}

// Purge all cache when theme/plugin is switched
add_action('switch_theme', 'wgr_purge_all_cache');
add_action('activated_plugin', 'wgr_purge_all_cache');
add_action('deactivated_plugin', 'wgr_purge_all_cache');

function wgr_purge_all_cache()
{
    wp_cache_flush();
}

/**
 * WordPress Object Cache API Functions
 */

function wp_cache_add($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;
    return $wp_object_cache->add($key, $data, $group, (int) $expire);
}

function wp_cache_set($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;
    return $wp_object_cache->set($key, $data, $group, (int) $expire);
}

function wp_cache_get($key, $group = '', $force = false, &$found = null)
{
    global $wp_object_cache;
    return $wp_object_cache->get($key, $group, $force, $found);
}

function wp_cache_delete($key, $group = '')
{
    global $wp_object_cache;
    return $wp_object_cache->delete($key, $group);
}

function wp_cache_flush()
{
    global $wp_object_cache;
    return $wp_object_cache->flush();
}

function wp_cache_replace($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;
    return $wp_object_cache->replace($key, $data, $group, (int) $expire);
}

function wp_cache_incr($key, $offset = 1, $group = '')
{
    global $wp_object_cache;
    return $wp_object_cache->incr($key, $offset, $group);
}

function wp_cache_decr($key, $offset = 1, $group = '')
{
    global $wp_object_cache;
    return $wp_object_cache->decr($key, $offset, $group);
}

function wp_cache_add_non_persistent_groups($groups)
{
    global $wp_object_cache;
    $wp_object_cache->add_non_persistent_groups($groups);
}

function wp_cache_switch_to_blog($blog_id)
{
    global $wp_object_cache;
    $wp_object_cache->switch_to_blog($blog_id);
}

function wp_cache_close()
{
    global $wp_object_cache;
    return $wp_object_cache->close();
}

function wp_cache_init()
{
    global $wp_object_cache;
    if (!is_object($wp_object_cache)) {
        $wp_object_cache = new WGR_Object_Cache();
    }
}

// Helper function to check if we should suspend cache addition
if (!function_exists('wp_suspend_cache_addition')) {
    function wp_suspend_cache_addition($suspend = null)
    {
        static $_suspend = false;

        if (is_bool($suspend)) {
            $_suspend = $suspend;
        }

        return $_suspend;
    }
}

// Helper function for serialization
if (!function_exists('maybe_serialize')) {
    function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }
        return $data;
    }
}

// Helper function for unserialization
if (!function_exists('maybe_unserialize')) {
    function maybe_unserialize($data)
    {
        if (is_serialized($data)) {
            return @unserialize($data);
        }
        return $data;
    }
}

// Helper function to check if data is serialized
if (!function_exists('is_serialized')) {
    function is_serialized($data, $strict = true)
    {
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace     = strpos($data, '}');
            if (false === $semicolon && false === $brace) {
                return false;
            }
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }
}
