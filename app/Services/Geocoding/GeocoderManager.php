<?php

namespace App\Services\Geocoding;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;

class GeocoderManager
{
    /** @var array<string, Geocoder> */
    private array $resolved = [];

    public function __construct(
        private readonly Config $config,
        private readonly Cache $cache,
    ) {}

    public function default(): Geocoder
    {
        return $this->driver((string) $this->config->get('bmlt.geocoder.default', 'nominatim'));
    }

    public function driver(string $name): Geocoder
    {
        return $this->resolved[$name] ??= $this->resolve($name);
    }

    private function resolve(string $name): Geocoder
    {
        $ttl = (int) $this->config->get('bmlt.geocoder.cache_ttl', 86400);

        return match ($name) {
            'null', 'none', '' => new NullGeocoder(),
            'nominatim' => new NominatimGeocoder(
                $this->config->get('bmlt.geocoder.drivers.nominatim'),
                $this->cache,
                $ttl,
            ),
            'google' => new GoogleGeocoder(
                $this->config->get('bmlt.geocoder.drivers.google'),
                $this->cache,
                $ttl,
            ),
            default => throw new GeocodingException("Unknown geocoder driver: {$name}"),
        };
    }
}
