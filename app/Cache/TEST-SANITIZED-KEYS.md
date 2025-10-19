# Test Sanitized Cache Keys

## 🎯 So sánh Cache Key: MD5 vs Sanitized

### ❌ TRƯỚC (dùng MD5):

```
URL: /san-pham/giay-the-thao-nam
Cache Key: mysite_page:d:a1b2c3d4e5f6g7h8i9j0

❌ Không biết URL gốc là gì
❌ Khó debug
❌ Không thể purge thủ công dễ dàng
```

### ✅ SAU (dùng Sanitized):

```
URL: /san-pham/giay-the-thao-nam
Cache Key: mysite_page:d:/san-pham/giay-the-thao-nam

✅ Nhìn thấy URL gốc
✅ Dễ debug
✅ Purge thủ công dễ dàng
```

## 📋 Test Cases

### Test 1: URL tiếng Việt có dấu

```
Input:  /sản-phẩm/giày-thể-thao-nam
Output: /san-pham/giay-the-thao-nam
```

### Test 2: URL có ký tự đặc biệt

```
Input:  /products/shoes@2024!#$%
Output: /products/shoes-2024
```

### Test 3: URL có khoảng trắng

```
Input:  /category/nike air max
Output: /category/nike-air-max
```

### Test 4: URL có chữ hoa

```
Input:  /Products/Nike-Shoes
Output: /products/nike-shoes
```

### Test 5: URL có nhiều dấu - liên tiếp

```
Input:  /products///shoes---new
Output: /products/shoes-new
```

### Test 6: URL rất dài (> 200 chars)

```
Input:  /very/long/url/with/many/segments/...
Output: /very/long/url/with/many/segme...-a1b2c3d4 (truncate + md5)
```

### Test 7: URL có query string (sau khi remove tracking params)

```
Input:  /products?sort=price&color=red
Output: /products-sort-price-color-red
```

## 🧪 Testing Script

Create `test-sanitized-cache-keys.php`:

```php
<?php

function test_sanitize_uri($uri) {
    echo "Input:  " . $uri . "\n";
    echo "Output: " . sanitize_uri_for_cache_key($uri) . "\n";
    echo "---\n";
}

function sanitize_uri_for_cache_key($uri) {
    // Chuyển về lowercase
    $uri = strtolower($uri);

    // Loại bỏ dấu tiếng Việt
    $uri = remove_vietnamese_accents($uri);

    // Chỉ giữ lại: a-z, 0-9, -, _, /
    $uri = preg_replace('/[^a-z0-9\-_\/]/', '-', $uri);

    // Loại bỏ nhiều dấu - liên tiếp
    $uri = preg_replace('/-+/', '-', $uri);

    // Loại bỏ dấu - ở đầu/cuối
    $uri = trim($uri, '-');

    // Giới hạn độ dài
    if (strlen($uri) > 200) {
        $uri = substr($uri, 0, 180) . '-' . md5(substr($uri, 180));
    }

    return $uri;
}

function remove_vietnamese_accents($str) {
    $accents = [
        'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
        'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
        'ì','í','ị','ỉ','ĩ',
        'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
        'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
        'ỳ','ý','ỵ','ỷ','ỹ',
        'đ'
    ];

    $no_accents = [
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
        'd'
    ];

    return str_replace($accents, $no_accents, $str);
}

echo "=== Test Sanitized Cache Keys ===\n\n";

test_sanitize_uri('/sản-phẩm/giày-thể-thao-nam');
test_sanitize_uri('/products/shoes@2024!#$%');
test_sanitize_uri('/category/nike air max');
test_sanitize_uri('/Products/Nike-Shoes');
test_sanitize_uri('/products///shoes---new');
test_sanitize_uri('/products?sort=price&color=red');
test_sanitize_uri('/về-chúng-tôi/liên-hệ');

// Test very long URL
$long_url = '/very/long/url/' . str_repeat('segment/', 30);
echo "Long URL test:\n";
test_sanitize_uri($long_url);
```

Run:

```bash
php test-sanitized-cache-keys.php
```

## 📊 Expected Output

```
=== Test Sanitized Cache Keys ===

Input:  /sản-phẩm/giày-thể-thao-nam
Output: /san-pham/giay-the-thao-nam
---
Input:  /products/shoes@2024!#$%
Output: /products/shoes-2024
---
Input:  /category/nike air max
Output: /category/nike-air-max
---
Input:  /Products/Nike-Shoes
Output: /products/nike-shoes
---
Input:  /products///shoes---new
Output: /products/shoes-new
---
Input:  /products?sort=price&color=red
Output: /products-sort-price-color-red
---
Input:  /về-chúng-tôi/liên-hệ
Output: /ve-chung-toi/lien-he
---
Long URL test:
Input:  /very/long/url/segment/segment/...
Output: /very/long/url/segment/segment/.../segment/segm...-abc123def456
---
```

## 🔍 Verify in Redis CLI

```bash
# Connect to Redis
redis-cli

# List all page cache keys (readable now!)
KEYS mysite_page:*

# Example output:
# mysite_page:d:/
# mysite_page:d:/san-pham/giay-the-thao-nam
# mysite_page:d:/products/nike-shoes
# mysite_page:m:/san-pham/giay-the-thao-nam
```

## ✅ Benefits of Sanitized Keys

### 1. **Readable Keys**

```bash
# Before (MD5):
GET mysite_page:d:a1b2c3d4e5f6
# → Không biết URL gốc

# After (Sanitized):
GET mysite_page:d:/san-pham/giay-the-thao-nam
# → Biết ngay URL gốc!
```

### 2. **Easy Debugging**

```bash
# Find all product pages:
KEYS mysite_page:*:/san-pham/*

# Find specific page:
KEYS mysite_page:*:/giay-the-thao-nam
```

### 3. **Manual Purge**

```bash
# Purge specific URL:
DEL mysite_page:d:/san-pham/giay-the-thao-nam
DEL mysite_page:m:/san-pham/giay-the-thao-nam

# Purge all products:
DEL $(redis-cli KEYS "mysite_page:*:/san-pham/*")
```

### 4. **Monitoring**

```bash
# Count product pages cached:
KEYS mysite_page:*:/san-pham/* | wc -l

# List all cached pages:
KEYS mysite_page:d:* | sort
```

## ⚠️ Edge Cases

### Case 1: Same URL, different params (after remove tracking)

```
URL 1: /page?utm_source=facebook
URL 2: /page?utm_source=google
URL 3: /page

All 3 → Same cache key: mysite_page:d:/page
✅ Expected behavior
```

### Case 2: Very long URL

```
URL: /very/long/url/with/many/segments/...
Cache Key: /very/long/url/with/many-abc123def
✅ Truncated + MD5 hash
```

### Case 3: Special characters

```
URL: /products/!@#$%^&*()
Cache Key: /products/-
✅ All special chars removed
```

## 🎯 Real World Examples

### Homepage:

```
Cache Key: mysite_page:d:/
```

### Product Page:

```
URL: /sản-phẩm/giày-nike-air-max-2024
Cache Key: mysite_page:d:/san-pham/giay-nike-air-max-2024
```

### Category Page:

```
URL: /danh-mục/thể-thao
Cache Key: mysite_page:d:/danh-muc/the-thao
```

### Blog Post:

```
URL: /blog/hướng-dẫn-chọn-giày-chạy-bộ
Cache Key: mysite_page:d:/blog/huong-dan-chon-giay-chay-bo
```

### Mobile Version:

```
URL: /sản-phẩm/giày-nike (mobile)
Cache Key: mysite_page:m:/san-pham/giay-nike
```

## 🔧 Troubleshooting

### Problem: Cache not working

```bash
# Check if key exists
EXISTS mysite_page:d:/san-pham/giay-nike

# Check TTL
TTL mysite_page:d:/san-pham/giay-nike

# Get content
GET mysite_page:d:/san-pham/giay-nike
```

### Problem: Duplicate cache entries

```bash
# List all similar keys
KEYS mysite_page:*:/san-pham/giay-nike*

# Should only have 2 (mobile + desktop)
```

### Problem: Memory usage high

```bash
# Count all cache entries
KEYS mysite_page:* | wc -l

# Check memory usage
INFO memory

# Find largest keys
MEMORY USAGE mysite_page:d:/san-pham/giay-nike
```
