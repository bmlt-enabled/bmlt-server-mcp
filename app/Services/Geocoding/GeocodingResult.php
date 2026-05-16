<?php

namespace App\Services\Geocoding;

class GeocodingResult
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly string $displayName,
        public readonly string $provider,
    ) {}

    /**
     * @return array{latitude: float, longitude: float, display_name: string, provider: string}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'display_name' => $this->displayName,
            'provider' => $this->provider,
        ];
    }
}
