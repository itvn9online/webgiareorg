<?php

/**
 * Advanced Page Cache with Redis
 * 
 * This drop-in caches full page HTML output using Redis.
 * It loads BEFORE WordPress, so it's extremely fast.
 * 
 * @package WebGiaRe
 * @link https://webgiare.org
 * @license GNU General Public License v2 or later (GPLv2+)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Cache Handler Class
 */
class WGR_Advanced_Cache
{
    private $redis;
    private $redis_connected = false;
    private $cache_key;
    private $cache_enabled = true;
    private $debug_mode = false;
    private $start_time;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->start_time = microtime(true);

        // Check if caching should be enabled
        if (!$this->should_cache()) {
            $this->cache_enabled = false;
            return;
        }

        // Initialize Redis connection
        $this->init_redis();

        // Generate cache key
        $this->cache_key = $this->get_cache_key();
    }

    /**
     * Check if page should be cached
     */
    private function should_cache()
    {
        // Don't cache POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return false;
        }

        // Don't cache if constants not defined
        if (!defined('WGR_REDIS_CACHE') || WGR_REDIS_CACHE !== true) {
            return false;
        }

        if (!defined('WGR_CACHE_PREFIX') || empty(WGR_CACHE_PREFIX)) {
            return false;
        }

        // Don't cache admin pages
        if (is_admin() || (defined('WP_ADMIN') && WP_ADMIN)) {
            return false;
        }

        // Don't cache AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return false;
        }

        // Don't cache REST API requests
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return false;
        }

        // Don't cache cron jobs
        if (defined('DOING_CRON') && DOING_CRON) {
            return false;
        }

        // Don't cache if user is logged in
        if ($this->is_user_logged_in()) {
            return false;
        }

        // Don't cache URLs with meaningful query strings
        // Tracking params (utm_*, fbclid, gclid) will be ignored in cache key
        if (!empty($_GET)) {
            $ignored_params = $this->get_ignored_query_params();
            $query_params = array_keys($_GET);
            $meaningful_params = array_diff($query_params, $ignored_params);

            // Nếu có params có ý nghĩa (không phải tracking) → Không cache
            if (!empty($meaningful_params)) {
                return false;
            }
        }

        // Don't cache if has WordPress cookies (comments, etc)
        foreach ($_COOKIE as $cookie_name => $cookie_value) {
            if (
                strpos($cookie_name, 'wordpress_') !== false ||
                strpos($cookie_name, 'wp-') !== false ||
                strpos($cookie_name, 'comment_') !== false
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get list of query params to ignore (tracking params)
     * These params will be removed from cache key
     */
    private function get_ignored_query_params()
    {
        return [
            // Google Analytics UTM parameters
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'utm_id',
            // Facebook Click ID
            'fbclid',
            // Google Click ID
            'gclid',
            'gclsrc',
            // Microsoft/Bing
            'msclkid',
            // Twitter
            'twclid',
            // LinkedIn
            'li_fat_id',
            // TikTok
            'ttclid',
            // Pinterest
            'epik',
            // Other common tracking
            'mc_cid',      // MailChimp
            'mc_eid',      // MailChimp
            '_ga',         // Google Analytics
            '_gl',         // Google Linker
            'ref',         // Generic referrer
            'source',      // Generic source
            'fb_comment_id',
            'add_to_wishlist',
            '_wpnonce',
            'v',
        ];
    }

    /**
     * Check if user is logged in (without loading WordPress)
     */
    private function is_user_logged_in()
    {
        // Check for WordPress auth cookies
        foreach ($_COOKIE as $cookie_name => $cookie_value) {
            if (strpos($cookie_name, 'wordpress_logged_in_') === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Initialize Redis connection
     */
    private function init_redis()
    {
        if (!class_exists('Redis')) {
            return;
        }

        try {
            $this->redis = new Redis();

            $host = defined('WGR_REDIS_HOST') ? WGR_REDIS_HOST : '127.0.0.1';
            $port = defined('WGR_REDIS_PORT') ? WGR_REDIS_PORT : 6379;
            $timeout = defined('WGR_REDIS_TIMEOUT') ? WGR_REDIS_TIMEOUT : 1;

            $this->redis->connect($host, $port, $timeout);

            // Set password if defined
            if (defined('WGR_REDIS_PASSWORD') && !empty(WGR_REDIS_PASSWORD)) {
                $this->redis->auth(WGR_REDIS_PASSWORD);
            }

            // Select database if defined
            if (defined('WGR_REDIS_DATABASE')) {
                $this->redis->select(WGR_REDIS_DATABASE);
            }

            $this->redis_connected = true;
        } catch (Exception $e) {
            $this->redis_connected = false;
            $this->log_error('Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique cache key for current request
     */
    private function get_cache_key()
    {
        $prefix = WGR_CACHE_PREFIX;
        $uri = $_SERVER['REQUEST_URI'];

        // Remove tracking query params from URI before generating cache key
        $uri = $this->remove_tracking_params($uri);

        // Sanitize URI để làm cache key (thay vì md5)
        $sanitized_uri = $this->sanitize_uri_for_cache_key($uri);

        // Include mobile detection in cache key
        $device = $this->is_mobile() ? 'm' : 'd';

        return $prefix . 'page:' . $device . ':' . $sanitized_uri;
    }

    /**
     * Sanitize URI to create cache key
     * Chỉ giữ lại: chữ thường không dấu, số, gạch ngang
     */
    private function sanitize_uri_for_cache_key($uri)
    {
        // Xử lý HTML entities và URL encoding bị lỗi
        // &amp; → &
        // &amp%3B → &
        $uri = str_replace(['&amp;', '&amp%3B'], '&', $uri);

        // URL decode để xử lý các ký tự encoded
        // $uri = urldecode($uri);

        // Loại bỏ dấu tiếng Việt
        $uri = $this->remove_vietnamese_accents($uri);

        // Chuyển về lowercase
        $uri = strtolower($uri);

        // Chỉ giữ lại: a-z, 0-9, -
        $uri = preg_replace('/[^a-z0-9\-]/', '-', $uri);

        // Loại bỏ nhiều dấu - liên tiếp
        $uri = preg_replace('/-+/', '-', $uri);

        // Loại bỏ dấu - ở đầu/cuối
        $uri = trim($uri, '-');

        // Xử lý đặc biệt cho trang chủ
        if (empty($uri)) {
            return '-';
        }
        // Giới hạn độ dài (Redis key nên < 256 chars)
        else if (strlen($uri) > 200) {
            // Nếu quá dài: lấy 180 ký tự đầu + md5(phần còn lại)
            $uri = substr($uri, 0, 180) . '-' . md5(substr($uri, 180));
        }

        return $uri;
    }

    /**
     * Remove Vietnamese accents
     */
    private function remove_vietnamese_accents($str)
    {
        $accents = [
            'à',
            'á',
            'ạ',
            'ả',
            'ã',
            'â',
            'ầ',
            'ấ',
            'ậ',
            'ẩ',
            'ẫ',
            'ă',
            'ằ',
            'ắ',
            'ặ',
            'ẳ',
            'ẵ',
            'è',
            'é',
            'ẹ',
            'ẻ',
            'ẽ',
            'ê',
            'ề',
            'ế',
            'ệ',
            'ể',
            'ễ',
            'ì',
            'í',
            'ị',
            'ỉ',
            'ĩ',
            'ò',
            'ó',
            'ọ',
            'ỏ',
            'õ',
            'ô',
            'ồ',
            'ố',
            'ộ',
            'ổ',
            'ỗ',
            'ơ',
            'ờ',
            'ớ',
            'ợ',
            'ở',
            'ỡ',
            'ù',
            'ú',
            'ụ',
            'ủ',
            'ũ',
            'ư',
            'ừ',
            'ứ',
            'ự',
            'ử',
            'ữ',
            'ỳ',
            'ý',
            'ỵ',
            'ỷ',
            'ỹ',
            'đ',
            'À',
            'Á',
            'Ạ',
            'Ả',
            'Ã',
            'Â',
            'Ầ',
            'Ấ',
            'Ậ',
            'Ẩ',
            'Ẫ',
            'Ă',
            'Ằ',
            'Ắ',
            'Ặ',
            'Ẳ',
            'Ẵ',
            'È',
            'É',
            'Ẹ',
            'Ẻ',
            'Ẽ',
            'Ê',
            'Ề',
            'Ế',
            'Ệ',
            'Ể',
            'Ễ',
            'Ì',
            'Í',
            'Ị',
            'Ỉ',
            'Ĩ',
            'Ò',
            'Ó',
            'Ọ',
            'Ỏ',
            'Õ',
            'Ô',
            'Ồ',
            'Ố',
            'Ộ',
            'Ổ',
            'Ỗ',
            'Ơ',
            'Ờ',
            'Ớ',
            'Ợ',
            'Ở',
            'Ỡ',
            'Ù',
            'Ú',
            'Ụ',
            'Ủ',
            'Ũ',
            'Ư',
            'Ừ',
            'Ứ',
            'Ự',
            'Ử',
            'Ữ',
            'Ỳ',
            'Ý',
            'Ỵ',
            'Ỷ',
            'Ỹ',
            'Đ'
        ];

        $no_accents = [
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'y',
            'y',
            'y',
            'd',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'y',
            'y',
            'y',
            'd'
        ];

        return str_replace($accents, $no_accents, $str);
    }

    /**
     * Remove tracking params from URI
     */
    private function remove_tracking_params($uri)
    {
        // Parse URI
        $parts = parse_url($uri);

        // Nếu không có query string → return ngay
        if (empty($parts['query'])) {
            return $uri;
        }

        // Parse query string
        parse_str($parts['query'], $params);

        // Lấy danh sách tracking params cần ignore
        $ignored_params = $this->get_ignored_query_params();

        // Loại bỏ tracking params
        $clean_params = array_diff_key($params, array_flip($ignored_params));

        // Nếu không còn params nào → trả về URI không có query string
        if (empty($clean_params)) {
            return $parts['path'];
        }

        // Build lại query string từ params đã clean
        $clean_query = http_build_query($clean_params);

        // Return URI đã clean
        return $parts['path'] . '?' . $clean_query;
    }

    /**
     * Check if mobile device
     */
    private function is_mobile()
    {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile/i', $user_agent);
    }

    /**
     * Check if SSL
     */
    private function is_ssl()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Try to serve cached page
     */
    public function serve_cache()
    {
        if (!$this->cache_enabled || !$this->redis_connected) {
            return false;
        }

        try {
            $cached = $this->redis->get($this->cache_key);

            if ($cached !== false) {
                // Add cache headers
                $this->send_cache_headers(true);

                // Add debug comment
                if ($this->debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
                    $elapsed = round((microtime(true) - $this->start_time) * 1000, 2);
                    echo "<!-- Served from Redis Cache in {$elapsed}ms -->\n";
                }

                echo $cached;
                exit;
            }
        } catch (Exception $e) {
            $this->log_error('Cache retrieve failed: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Start output buffering to capture page content
     */
    public function start_caching()
    {
        if (!$this->cache_enabled || !$this->redis_connected) {
            return;
        }

        ob_start([$this, 'save_cache']);
    }

    /**
     * Save captured content to cache
     */
    public function save_cache($content)
    {
        if (!$this->cache_enabled || !$this->redis_connected) {
            return $content;
        }

        // Only cache if content is HTML and not empty
        if (empty($content) || strlen($content) < 100) {
            return $content;
        }

        // Check if content is HTML
        if (stripos($content, '<!DOCTYPE html') === false && stripos($content, '<html') === false) {
            return $content;
        }

        try {
            // Default TTL: 1 hour (3600 seconds)
            $ttl = defined('WGR_PAGE_CACHE_TTL') ? WGR_PAGE_CACHE_TTL : 3600;

            // Save to Redis
            $this->redis->setex($this->cache_key, $ttl, $content);

            // Add debug comment
            if ($this->debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
                $elapsed = round((microtime(true) - $this->start_time) * 1000, 2);
                $content .= "\n<!-- Cached by Advanced Cache (Generated in {$elapsed}ms, TTL: {$ttl}s) -->";
            }
        } catch (Exception $e) {
            $this->log_error('Cache save failed: ' . $e->getMessage());
        }

        return $content;
    }

    /**
     * Send cache-related HTTP headers
     */
    private function send_cache_headers($from_cache = false)
    {
        if (headers_sent()) {
            return;
        }

        if ($from_cache) {
            header('X-Cache: HIT');
            header('X-Cache-Engine: WGR-Redis');
        } else {
            header('X-Cache: MISS');
        }

        // Set cache control headers
        $max_age = defined('WGR_PAGE_CACHE_TTL') ? WGR_PAGE_CACHE_TTL : 3600;
        header('Cache-Control: public, max-age=' . $max_age);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $max_age) . ' GMT');
    }

    /**
     * Purge cache for specific URL
     */
    public function purge_url($url)
    {
        if (!$this->redis_connected) {
            return false;
        }

        try {
            $uri = parse_url($url, PHP_URL_PATH);
            if (!$uri) {
                return false;
            }

            // Sanitize URI giống như khi tạo cache key
            $sanitized_uri = $this->sanitize_uri_for_cache_key($uri);

            // Purge both mobile and desktop versions
            foreach (['m', 'd'] as $device) {
                $key = WGR_CACHE_PREFIX . 'page:' . $device . ':' . $sanitized_uri;
                $this->redis->del($key);
            }

            return true;
        } catch (Exception $e) {
            $this->log_error('Cache purge failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Purge all page cache
     */
    public function purge_all()
    {
        if (!$this->redis_connected) {
            return false;
        }

        try {
            $pattern = WGR_CACHE_PREFIX . 'page:*';
            $keys = $this->redis->keys($pattern);

            if (!empty($keys)) {
                $this->redis->del($keys);
            }

            return true;
        } catch (Exception $e) {
            $this->log_error('Cache purge all failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log error
     */
    private function log_error($message)
    {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[WGR Advanced Cache] ' . $message);
        }
    }
}

// Initialize cache handler
$wgr_advanced_cache = new WGR_Advanced_Cache();

// Try to serve cached page (before WordPress loads)
$wgr_advanced_cache->serve_cache();

// If not cached, start output buffering to capture content
$wgr_advanced_cache->start_caching();

// Hook to purge cache on post/page updates
add_action('save_post', function ($post_id) use ($wgr_advanced_cache) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    // Purge the post URL
    $url = get_permalink($post_id);
    if ($url) {
        $wgr_advanced_cache->purge_url($url);
    }

    // Purge home page
    $wgr_advanced_cache->purge_url(home_url('/'));

    // Purge category/tag archives
    $post_type = get_post_type($post_id);
    if ($post_type === 'post') {
        $categories = get_the_category($post_id);
        foreach ($categories as $category) {
            $wgr_advanced_cache->purge_url(get_category_link($category->term_id));
        }

        $tags = get_the_tags($post_id);
        if ($tags) {
            foreach ($tags as $tag) {
                $wgr_advanced_cache->purge_url(get_tag_link($tag->term_id));
            }
        }
    }
}, 10, 1);

// Hook to purge cache on comment updates
add_action('comment_post', function ($comment_id) use ($wgr_advanced_cache) {
    $comment = get_comment($comment_id);
    if ($comment) {
        $url = get_permalink($comment->comment_post_ID);
        if ($url) {
            $wgr_advanced_cache->purge_url($url);
        }
    }
}, 10, 1);

// Hook to purge all cache when theme/plugin changes
add_action('switch_theme', function () use ($wgr_advanced_cache) {
    $wgr_advanced_cache->purge_all();
});

add_action('activated_plugin', function () use ($wgr_advanced_cache) {
    $wgr_advanced_cache->purge_all();
});

add_action('deactivated_plugin', function () use ($wgr_advanced_cache) {
    $wgr_advanced_cache->purge_all();
});
