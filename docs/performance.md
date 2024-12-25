# Performance Optimization

## Caching Considerations

```php
// Example using PSR-6 cache
public function verify(VerifyConfiguration $config): Response
{
    $cacheKey = 'turnstile_' . md5((string) $config->getToken());

    // Check cache first
    if ($cachedResponse = $this->cache->getItem($cacheKey)->get()) {
        return $cachedResponse;
    }
    
    $response = $this->turnstile->verify($config);
    
    // Cache successful responses briefly
    if ($response->isSuccess()) {
        $cacheItem = $this->cache->getItem($cacheKey)
            ->set($response)
            ->expiresAfter(300); // 5 minutes
        $this->cache->save($cacheItem);
    }
    
    return $response;
}
```

## Rate Limiting

```php
// Example using Redis
public function verify(VerifyConfiguration $config): Response
{
    $ip = $config->getRemoteIp();
    $key = 'turnstile_ratelimit_' . $ip;

    if ($this->redis->get($key) > 10) {
        throw new TooManyRequestsException();
    }
    
    $this->redis->incr($key);
    $this->redis->expire($key, 300); // Reset after 5 minutes
    
    return $this->turnstile->verify($config);
}
```
