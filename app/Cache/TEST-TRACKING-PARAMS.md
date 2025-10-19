# Test Tracking Params Handling

## 🧪 Test Case: Cache Key Generation

### Scenario 1: Different tracking params → Same cache

```bash
# Request 1: UTM from Facebook
curl https://example.com/page?utm_source=facebook&utm_campaign=summer

# Request 2: UTM from Google
curl https://example.com/page?utm_source=google&utm_campaign=winter

# Request 3: No params
curl https://example.com/page

# Request 4: Facebook Click ID
curl https://example.com/page?fbclid=IwAR123abc

# Request 5: Google Click ID
curl https://example.com/page?gclid=Cj0KCQ123abc
```

**Expected Result:**

- ✅ All 5 requests use **SAME cache**
- ✅ Cache key: `mysite_page:https:desktop:md5(/page)`
- ✅ First request: `X-Cache: MISS` (generate cache)
- ✅ Next 4 requests: `X-Cache: HIT` (from cache)

### Scenario 2: Meaningful params → Different cache

```bash
# Request 1: Search query
curl https://example.com/search?q=keyword

# Request 2: Different search
curl https://example.com/search?q=another

# Request 3: Product filter
curl https://example.com/products?sort=price
```

**Expected Result:**

- ❌ Each request creates **DIFFERENT cache** (or NOT cached)
- ❌ Cache disabled because has meaningful query params

### Scenario 3: Mixed params → NOT cached

```bash
# Request: Meaningful + Tracking params
curl https://example.com/products?sort=price&utm_source=google
```

**Expected Result:**

- ❌ **NOT cached** because has meaningful param (`sort=price`)
- ❌ Even though also has tracking param (`utm_source`)

## 📊 Testing Script

Create `test-tracking-params.sh`:

```bash
#!/bin/bash

BASE_URL="https://your-site.com/sample-page"

echo "=== Test 1: UTM Params (should use same cache) ==="
echo "Request 1: utm_source=facebook"
curl -s -I "$BASE_URL?utm_source=facebook&utm_campaign=summer" | grep "X-Cache"

echo "Request 2: utm_source=google"
curl -s -I "$BASE_URL?utm_source=google&utm_campaign=winter" | grep "X-Cache"

echo "Request 3: no params"
curl -s -I "$BASE_URL" | grep "X-Cache"

echo ""
echo "=== Test 2: Click IDs (should use same cache) ==="
echo "Request 1: fbclid"
curl -s -I "$BASE_URL?fbclid=IwAR123abc" | grep "X-Cache"

echo "Request 2: gclid"
curl -s -I "$BASE_URL?gclid=Cj0KCQ123abc" | grep "X-Cache"

echo ""
echo "=== Test 3: Meaningful Params (should NOT cache) ==="
echo "Request 1: search query"
curl -s -I "https://your-site.com/?s=keyword" | grep "X-Cache"

echo ""
echo "=== Test 4: Mixed Params (should NOT cache) ==="
echo "Request 1: sort + utm"
curl -s -I "https://your-site.com/products?sort=price&utm_source=google" | grep "X-Cache"
```

Run test:

```bash
chmod +x test-tracking-params.sh
./test-tracking-params.sh
```

## 🎯 Expected Output

```
=== Test 1: UTM Params (should use same cache) ===
Request 1: utm_source=facebook
X-Cache: MISS

Request 2: utm_source=google
X-Cache: HIT

Request 3: no params
X-Cache: HIT

=== Test 2: Click IDs (should use same cache) ===
Request 1: fbclid
X-Cache: HIT

Request 2: gclid
X-Cache: HIT

=== Test 3: Meaningful Params (should NOT cache) ===
Request 1: search query
(no X-Cache header)

=== Test 4: Mixed Params (should NOT cache) ===
Request 1: sort + utm
(no X-Cache header)
```

## 🔍 Verify in Redis

```bash
# Connect to Redis
redis-cli

# Check cache keys
KEYS mysite_page:*

# Expected: Only 1 key for all tracking param variations
# mysite_page:https:desktop:abc123...

# Get cache content
GET mysite_page:https:desktop:abc123...

# Check TTL
TTL mysite_page:https:desktop:abc123...
```

## 📈 Benefits

### Before (tracking params create separate cache):

```
Cache entries:
- /page
- /page?utm_source=facebook
- /page?utm_source=google
- /page?fbclid=xxx
- /page?gclid=yyy

Total: 5 cache entries = 5x memory usage
Hit ratio: 20% (each variant = separate cache)
```

### After (tracking params ignored):

```
Cache entries:
- /page (all variants use this)

Total: 1 cache entry = 1x memory usage
Hit ratio: 80%+ (all variants share cache)
```

## 🎁 Real World Example

Campaign: Summer Sale 2025

**Marketing sends traffic from:**

1. Facebook Ads: `?utm_source=facebook&utm_campaign=summer_sale`
2. Google Ads: `?utm_source=google&utm_campaign=summer_sale`
3. Email: `?utm_source=email&utm_campaign=summer_sale`
4. Twitter: `?utm_source=twitter&utm_campaign=summer_sale`

**Without tracking params removal:**

- 4 separate cache entries
- 75% cache miss ratio
- 4x memory usage

**With tracking params removal:**

- 1 cache entry (shared)
- 25% cache miss ratio (only first request)
- 1x memory usage
- **Campaign tracking still works** (JavaScript records params)

## ✅ Validation Checklist

- [ ] Same page with different UTM params returns `X-Cache: HIT`
- [ ] Same page with fbclid/gclid returns `X-Cache: HIT`
- [ ] Page with search query does NOT cache
- [ ] Page with filter params does NOT cache
- [ ] Redis has minimal cache entries
- [ ] Campaign tracking still works in Google Analytics
- [ ] Cache hit ratio > 80%
