<?php

namespace App\Services\Geocoding;

class NullGeocoder implements Geocoder
{
    public function geocode(string $address): ?GeocodingResult
    {
        throw new GeocodingException(
            'Server-side geocoding is disabled (GEOCODER=null). '
            .'Pass lat/lng coordinates instead of an address, '
            .'or configure the nominatim or google geocoder driver.'
        );
    }

    public function name(): string
    {
        return 'null';
    }
}
