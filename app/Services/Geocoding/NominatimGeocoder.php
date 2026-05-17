<?php

namespace App\Services\Geocoding;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Http;

class NominatimGeocoder implements Geocoder
{
    /**
     * @param  array{endpoint: string, user_agent: string, rate_limit_ms: int}  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly Cache $cache,
        private readonly int $cacheTtl = 86400,
    ) {}

    public function geocode(string $address): ?GeocodingResult
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $key = 'geocode:nominatim:'.sha1($address);

        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached === '__miss__' ? null : $this->hydrate($cached);
        }

        $this->throttle($key);

        $response = Http::withHeaders([
            'User-Agent' => $this->config['user_agent'],
            'Accept' => 'application/json',
        ])->timeout(10)->get($this->config['endpoint'], [
            'q' => $address,
            'format' => 'jsonv2',
            'limit' => 1,
            'addressdetails' => 0,
        ]);

        if (! $response->successful()) {
            throw new GeocodingException("Nominatim returned HTTP {$response->status()}");
        }

        $hits = $response->json();
        if (! is_array($hits) || $hits === []) {
            $this->cache->put($key, '__miss__', $this->cacheTtl);

            return null;
        }

        $hit = $hits[0];
        $payload = [
            'lat' => (float) $hit['lat'],
            'lon' => (float) $hit['lon'],
            'display_name' => (string) ($hit['display_name'] ?? $address),
        ];

        $this->cache->put($key, $payload, $this->cacheTtl);

        return $this->hydrate($payload);
    }

    public function name(): string
    {
        return 'nominatim';
    }

    /**
     * Nominatim's free tier requires <= 1 req/sec. Use a cache-based mutex
     * so concurrent requests serialize.
     */
    private function throttle(string $key): void
    {
        $waitMs = max(0, $this->config['rate_limit_ms']);
        if ($waitMs === 0) {
            return;
        }

        $lockKey = 'geocode:nominatim:lock';
        $last = (int) ($this->cache->get($lockKey, 0));
        $nowMs = (int) (microtime(true) * 1000);
        $sleep = ($last + $waitMs) - $nowMs;
        if ($sleep > 0) {
            usleep($sleep * 1000);
        }
        $this->cache->put($lockKey, (int) (microtime(true) * 1000), 60);
    }

    /**
     * @param  array{lat: float, lon: float, display_name: string}  $payload
     */
    private function hydrate(array $payload): GeocodingResult
    {
        return new GeocodingResult(
            latitude: $payload['lat'],
            longitude: $payload['lon'],
            displayName: $payload['display_name'],
            provider: 'nominatim',
        );
    }
}
