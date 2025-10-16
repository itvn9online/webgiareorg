# Object Cache Benchmark Guide

## 1. Cài đặt Query Monitor Plugin

```bash
# Hoặc từ WordPress Admin > Plugins > Add New > Search "Query Monitor"
```

## 2. Test Scenario

### A. Test KHÔNG có Object Cache

1. Tắt cache trong `wp-config.php`:

```php
define('WGR_REDIS_CACHE', false);
// hoặc comment lại
// define('WGR_REDIS_CACHE', true);
```

2. Clear cache:

```bash
redis-cli FLUSHALL
```

3. Reload homepage 5 lần, ghi lại metrics:

| Metric             | Lần 1 | Lần 2 | Lần 3 | Lần 4 | Lần 5 | Trung bình |
| ------------------ | ----- | ----- | ----- | ----- | ----- | ---------- |
| DB Queries         |       |       |       |       |       |            |
| Page Load Time (s) |       |       |       |       |       |            |
| Memory Usage (MB)  |       |       |       |       |       |            |

### B. Test CÓ Object Cache

1. Bật cache trong `wp-config.php`:

```php
define('WGR_REDIS_CACHE', true);
define('WGR_CACHE_PREFIX', 'test_');
define('WGR_REDIS_HOST', '127.0.0.1');
define('WGR_REDIS_PORT', 6379);
```

2. Clear cache để test cold start:

```bash
redis-cli FLUSHALL
```

3. Reload homepage 5 lần:

| Metric             | Lần 1 (Cold) | Lần 2 | Lần 3 | Lần 4 | Lần 5 | Trung bình |
| ------------------ | ------------ | ----- | ----- | ----- | ----- | ---------- |
| DB Queries         |              |       |       |       |       |            |
| Cache Hits         |              |       |       |       |       |            |
| Cache Misses       |              |       |       |       |       |            |
| Hit Ratio (%)      |              |       |       |       |       |            |
| Page Load Time (s) |              |       |       |       |       |            |
| Memory Usage (MB)  |              |       |       |       |       |            |

## 3. Expected Results

### Không có cache:

- DB Queries: **100-200 queries** mỗi lần
- Consistent performance (không cải thiện qua các lần load)

### Có cache (sau warm up):

- DB Queries: **20-50 queries** (giảm 50-80%)
- Cache Hit Ratio: **80-95%**
- Page Load Time: Nhanh hơn **20-50%**
- Memory Usage: Tăng nhẹ do cache trong RAM

## 4. Lý do tốc độ không khác biệt rõ rệt

### Nếu bạn không thấy khác biệt, có thể do:

1. **Database đã rất nhanh:**

   - Local development với MySQL trên cùng máy
   - Database nhỏ, ít data
   - → Cache chỉ giúp ít vì query đã nhanh

2. **Server resources dư thừa:**

   - RAM nhiều, CPU mạnh
   - Không có load cao
   - → Không thấy bottleneck

3. **Cache chưa warm up:**

   - Chỉ load 1-2 lần
   - Cache chưa kịp build up
   - → Cần load nhiều lần hơn

4. **Đo sai metric:**
   - Chỉ nhìn tổng thời gian load page
   - Không đo database queries
   - → Cần dùng Query Monitor

### Cache thực sự có ích khi:

- ✅ **Production** với nhiều concurrent users
- ✅ **Database remote** (không cùng server)
- ✅ **Database lớn** với nhiều joins
- ✅ **Complex queries** với nhiều calculations
- ✅ **High traffic** website

## 5. Cách đo chính xác hơn

### A. Dùng Apache Benchmark:

```bash
# Test 100 requests, 10 concurrent
ab -n 100 -c 10 http://localhost/

# Với cache OFF vs ON, so sánh:
# - Requests per second
# - Time per request
# - Failed requests
```

### B. Dùng wrk:

```bash
wrk -t4 -c100 -d30s http://localhost/
# So sánh requests/sec với cache ON vs OFF
```

### C. Monitor Redis:

```bash
# Xem số lượng keys trong Redis
redis-cli DBSIZE

# Monitor real-time commands
redis-cli MONITOR

# Xem hit/miss ratio
redis-cli INFO stats | grep keyspace
```

## 6. Kết luận

Object Cache hiệu quả nhất khi:

- Website có **traffic cao**
- Database queries **phức tạp**
- Database **không cùng server**
- Có nhiều **repeated queries**

Trong development local, sự khác biệt có thể không rõ ràng.
Nhưng trong production, cache có thể giảm 50-80% database load!
