<?php

namespace App\Services\Geocoding;

interface Geocoder
{
    /**
     * Resolve a free-form address to coordinates, or null if no match.
     */
    public function geocode(string $address): ?GeocodingResult;

    /**
     * Driver identifier (e.g. "nominatim", "google", "null").
     */
    public function name(): string;
}
