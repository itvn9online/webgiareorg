# Advanced Page Cache với Redis

## 📖 Giới thiệu

**Advanced Cache** là hệ thống cache toàn bộ HTML output của trang web, load **TRƯỚC** WordPress để đạt tốc độ cực nhanh.

## ⚡ Hiệu suất

- **Không có cache**: ~200-500ms
- **Object Cache**: ~100-200ms
- **Advanced Cache**: ~2-10ms ⚡

## 🎯 Cách hoạt động

```
Request → Advanced Cache → Redis Check
                          ├─ HIT → Return HTML (2-10ms) ✅
                          └─ MISS → WordPress → Generate → Save to Redis
```

## 🔧 Cài đặt

### 1. Thêm vào `wp-config.php`:

```php
// Required constants
define('WGR_REDIS_CACHE', true);
define('WGR_CACHE_PREFIX', 'mysite_');
define('WGR_REDIS_HOST', '127.0.0.1');
define('WGR_REDIS_PORT', 6379);

// Optional constants
define('WGR_REDIS_PASSWORD', '');          // Nếu Redis có password
define('WGR_REDIS_DATABASE', 0);           // Chọn database (0-15)
define('WGR_REDIS_TIMEOUT', 1);            // Timeout connection (giây)
define('WGR_PAGE_CACHE_TTL', 3600);        // TTL cache (giây), default 1 hour

// WordPress will auto-add this when enable Advanced Cache
define('WP_CACHE', true);
```

### 2. Enable trong Admin:

1. Vào **WGR Config** page
2. Check ✅ **WGR Advanced Cache**
3. Click **Lưu Options**

File `wp-content/advanced-cache.php` sẽ được tạo tự động.

## ✅ Trang được Cache

- ✅ Trang công khai (không đăng nhập)
- ✅ GET requests
- ✅ URLs có tracking params (utm\_\*, fbclid, gclid...) - **Tracking params sẽ bị loại bỏ khỏi cache key**
- ✅ Phân biệt Mobile/Desktop
- ✅ Phân biệt HTTP/HTTPS

### 🎯 Smart Tracking Params Handling

Các tracking params sau sẽ **bị loại bỏ** khỏi cache key (không ảnh hưởng cache):

**Google Analytics:**

- `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `utm_id`
- `gclid`, `gclsrc` (Google Ads)
- `_ga`, `_gl` (Google Analytics)

**Social Media:**

- `fbclid` (Facebook)
- `msclkid` (Microsoft/Bing)
- `twclid` (Twitter)
- `li_fat_id` (LinkedIn)
- `ttclid` (TikTok)
- `epik` (Pinterest)

**Email Marketing:**

- `mc_cid`, `mc_eid` (MailChimp)

**Other:**

- `ref`, `source` (Generic tracking)

**Ví dụ:**

```
URL 1: https://example.com/page?utm_source=facebook&utm_campaign=summer
URL 2: https://example.com/page?utm_source=google&utm_campaign=winter
URL 3: https://example.com/page

→ Cả 3 URLs đều dùng CÙNG cache (cache key: https://example.com/page)
```

Điều này giúp:

- ✅ Tăng cache hit ratio
- ✅ Giảm cache entries
- ✅ Tiết kiệm Redis memory
- ✅ Campaign tracking vẫn hoạt động (JavaScript ghi nhận params)

## ❌ Trang KHÔNG được Cache

- ❌ Admin pages
- ❌ AJAX requests
- ❌ REST API
- ❌ POST/PUT/DELETE requests
- ❌ User đã đăng nhập
- ❌ URLs có WordPress cookies
- ❌ URLs có **meaningful** query strings (không phải tracking params)

**Ví dụ URLs KHÔNG cache:**

```
❌ https://example.com/search?q=keyword          (search query)
❌ https://example.com/products?sort=price       (filter/sort)
❌ https://example.com/page?id=123               (dynamic param)
```

**Ví dụ URLs VẪN cache:**

```
✅ https://example.com/page?utm_source=google    (tracking only)
✅ https://example.com/page?fbclid=xxx           (tracking only)
✅ https://example.com/page                      (no params)
```

## 🔄 Auto Purge Cache

Cache tự động xóa khi:

1. **Save/Update Post**:

   - ✅ URL của post
   - ✅ Homepage
   - ✅ Category archives
   - ✅ Tag archives

2. **Comment mới**:

   - ✅ URL của post có comment

3. **Switch Theme**:

   - ✅ Xóa tất cả page cache

4. **Activate/Deactivate Plugin**:
   - ✅ Xóa tất cả page cache

## 📊 HTTP Headers

### Cache HIT (từ Redis):

```http
X-Cache: HIT
X-Cache-Engine: WGR-Redis
Cache-Control: public, max-age=3600
Expires: Thu, 20 Oct 2025 12:00:00 GMT
```

### Cache MISS (generate mới):

```http
X-Cache: MISS
```

## 🐛 Debug Mode

Khi `WP_DEBUG = true`, HTML sẽ có comment:

```html
<!-- Served from Redis Cache in 2.5ms -->
<!-- Cached by Advanced Cache (Generated in 156ms, TTL: 3600s) -->
```

## 🧪 Testing

### 1. Kiểm tra cache đang hoạt động:

```bash
# Request 1: Cache MISS
curl -I https://example.com/
# X-Cache: MISS

# Request 2: Cache HIT
curl -I https://example.com/
# X-Cache: HIT
```

### 2. Kiểm tra Redis:

```bash
# Connect to Redis CLI
redis-cli

# List all page cache keys
KEYS mysite_page:*

# Get cache content
GET mysite_page:https:desktop:abc123...

# Check TTL
TTL mysite_page:https:desktop:abc123...
```

### 3. Kiểm tra mobile/desktop:

```bash
# Desktop
curl -H "User-Agent: Mozilla/5.0" https://example.com/

# Mobile
curl -H "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)" https://example.com/
```

## ⚙️ Cấu hình nâng cao

### Tùy chỉnh TTL cho từng loại trang:

```php
// In your theme's functions.php
add_filter('wgr_page_cache_ttl', function($ttl) {
    if (is_front_page()) {
        return 7200; // Homepage: 2 hours
    }
    if (is_single()) {
        return 86400; // Single post: 24 hours
    }
    return 3600; // Default: 1 hour
});
```

### Loại trừ URLs khỏi cache:

```php
// In advanced-cache.php, modify should_cache() method
// Add your custom logic
if (strpos($_SERVER['REQUEST_URI'], '/my-custom-page/') !== false) {
    return false;
}
```

## 🔧 Manual Purge

### Purge specific URL:

```php
global $wgr_advanced_cache;
$wgr_advanced_cache->purge_url('https://example.com/sample-post/');
```

### Purge all:

```php
global $wgr_advanced_cache;
$wgr_advanced_cache->purge_all();
```

### Purge từ Redis CLI:

```bash
# Purge all page cache
redis-cli DEL $(redis-cli KEYS "mysite_page:*")

# Purge specific pattern
redis-cli DEL $(redis-cli KEYS "mysite_page:*:desktop:*")
```

## 📈 Performance Tips

### 1. Tăng TTL cho stable content:

```php
define('WGR_PAGE_CACHE_TTL', 86400); // 24 hours
```

### 2. Sử dụng CDN:

- Advanced Cache + CDN = Cực nhanh
- Cache tại Redis + Cache tại CDN edge

### 3. Monitor Redis memory:

```bash
redis-cli INFO memory
```

### 4. Set Redis maxmemory:

```bash
# In redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## ⚠️ Lưu ý

1. **Không dùng cho admin**: Advanced Cache tự động skip admin pages
2. **Personalized content**: Nếu trang có nội dung cá nhân hóa → Không nên cache
3. **E-commerce**: Cẩn thận với cart, checkout pages (đã auto skip)
4. **Testing**: Luôn test trên staging trước khi apply production
5. **Monitoring**: Monitor Redis memory usage

## 🆚 So sánh với các cache khác

| Feature       | Advanced Cache  | Object Cache     | WP Super Cache |
| ------------- | --------------- | ---------------- | -------------- |
| Load timing   | Before WP       | During WP        | After WP       |
| Speed         | ⚡⚡⚡ (2-10ms) | ⚡⚡ (100-200ms) | ⚡ (50-100ms)  |
| Memory        | Low             | Medium           | High           |
| Setup         | Easy            | Easy             | Complex        |
| Mobile detect | ✅ Yes          | ❌ No            | ✅ Yes         |

## 🔗 Links

- [Redis Documentation](https://redis.io/documentation)
- [WordPress Object Cache](https://developer.wordpress.org/reference/classes/wp_object_cache/)
- [WebGiaRe](https://webgiare.org)

## 📝 Changelog

### Version 1.0

- Initial release
- Full page HTML caching
- Mobile/Desktop detection
- Auto purge on content changes
- Debug mode
- HTTP cache headers
