<?php

namespace App\Services\Geocoding;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Http;

class GoogleGeocoder implements Geocoder
{
    /**
     * @param  array{endpoint: string, api_key: ?string, region: ?string, language: ?string}  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly Cache $cache,
        private readonly int $cacheTtl = 86400,
    ) {
        if (empty($this->config['api_key'])) {
            throw new GeocodingException(
                'Google geocoder requires GOOGLE_GEOCODER_API_KEY.'
            );
        }
    }

    public function geocode(string $address): ?GeocodingResult
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $key = 'geocode:google:' . sha1($address);
        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached === '__miss__' ? null : $this->hydrate($cached);
        }

        $params = array_filter([
            'address' => $address,
            'key' => $this->config['api_key'],
            'region' => $this->config['region'] ?? null,
            'language' => $this->config['language'] ?? null,
        ]);

        $response = Http::timeout(10)->get($this->config['endpoint'], $params);

        if (! $response->successful()) {
            throw new GeocodingException("Google geocoder returned HTTP {$response->status()}");
        }

        $data = $response->json();
        $status = $data['status'] ?? 'UNKNOWN_ERROR';

        if ($status === 'ZERO_RESULTS') {
            $this->cache->put($key, '__miss__', $this->cacheTtl);

            return null;
        }

        if ($status !== 'OK') {
            throw new GeocodingException("Google geocoder error: {$status}");
        }

        $hit = $data['results'][0];
        $payload = [
            'lat' => (float) $hit['geometry']['location']['lat'],
            'lon' => (float) $hit['geometry']['location']['lng'],
            'display_name' => (string) $hit['formatted_address'],
        ];

        $this->cache->put($key, $payload, $this->cacheTtl);

        return $this->hydrate($payload);
    }

    public function name(): string
    {
        return 'google';
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
            provider: 'google',
        );
    }
}
