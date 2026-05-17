<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesBmltClient;
use App\Services\Bmlt\BmltClientFactory;
use App\Services\Bmlt\BmltException;
use App\Services\Geocoding\GeocoderManager;
use App\Services\Geocoding\GeocodingException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('search_meetings')]
#[Title('Search Narcotics Anonymous meetings')]
#[Description(
    'Search Narcotics Anonymous meetings on a BMLT root server. Filter by location '
    .'(address or lat/lng + radius), weekday, time of day, format, venue type '
    .'(in-person / virtual / hybrid), and service body. Returns a trimmed summary '
    ."by default; pass data_format='full' for the raw BMLT response."
)]
#[IsReadOnly]
#[IsIdempotent]
class SearchMeetingsTool extends Tool
{
    use ResolvesBmltClient;

    private const SUMMARY_FIELDS = [
        'id_bigint', 'meeting_name', 'weekday_tinyint', 'start_time', 'duration_time',
        'time_zone', 'location_text', 'location_street', 'location_municipality',
        'location_province', 'location_postal_code_1', 'latitude', 'longitude',
        'formats', 'venue_type', 'distance_in_miles', 'distance_in_km',
        'virtual_meeting_link', 'phone_meeting_number', 'comments',
        'service_body_bigint',
    ];

    public function handle(
        Request $request,
        BmltClientFactory $factory,
        GeocoderManager $geocoders,
    ): Response {
        $client = $this->resolveClient($request, $factory);
        if ($client instanceof Response) {
            return $client;
        }

        $params = [];
        $coordsFromAddress = null;

        $lat = $request->get('latitude');
        $lng = $request->get('longitude');
        $address = trim((string) $request->get('address', ''));

        if (($lat === null || $lng === null) && $address !== '') {
            try {
                $result = $geocoders->default()->geocode($address);
            } catch (GeocodingException $e) {
                return Response::error($e->getMessage());
            }

            if ($result === null) {
                return Response::error("Could not geocode address: {$address}");
            }

            $lat = $result->latitude;
            $lng = $result->longitude;
            $coordsFromAddress = $result->toArray();
        }

        if ($lat !== null && $lng !== null) {
            $params['lat_val'] = $lat;
            $params['long_val'] = $lng;
            $radiusKm = $request->get('radius_km');
            if ($radiusKm !== null) {
                $params['geo_width_km'] = (float) $radiusKm;
            } else {
                $params['geo_width'] = (float) $request->get('radius_miles', 10);
            }
            $params['sort_results_by_distance'] = 1;
        }

        if ($weekdays = $request->get('weekdays')) {
            $params['weekdays'] = (array) $weekdays;
        }
        if ($formats = $request->get('format_ids')) {
            $params['formats'] = (array) $formats;
        }
        if ($venues = $request->get('venue_types')) {
            $params['venue_types'] = (array) $venues;
        }
        if ($services = $request->get('service_body_ids')) {
            $params['services'] = (array) $services;
            $params['recursive'] = $request->get('recursive_services', true) ? 1 : 0;
        }
        if ($text = $request->get('search_text')) {
            $params['SearchString'] = $text;
        }
        if ($language = $request->get('language')) {
            $params['lang_enum'] = $language;
        }
        if ($pageSize = $request->get('page_size')) {
            $params['page_size'] = (int) $pageSize;
            $params['page_num'] = (int) $request->get('page', 1);
        }

        $this->applyTime($params, 'StartsAfter', (string) $request->get('starts_after', ''));
        $this->applyTime($params, 'StartsBefore', (string) $request->get('starts_before', ''));

        try {
            $meetings = $client->searchMeetings($params);
        } catch (BmltException $e) {
            return Response::error($e->getMessage());
        }

        $dataFormat = $request->get('data_format', 'summary');
        if ($dataFormat === 'summary') {
            $meetings = array_map(fn ($m) => $this->summarize($m), $meetings);
        }

        return Response::json([
            'root_server' => $client->rootUrl(),
            'count' => count($meetings),
            'geocoded_from_address' => $coordsFromAddress,
            'meetings' => $meetings,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'address' => $schema->string()
                ->description('Free-form address to search near (geocoded server-side). Ignored if latitude+longitude are provided.'),

            'latitude' => $schema->number()
                ->description('Latitude in decimal degrees. Pair with longitude for an exact-coordinate search.'),

            'longitude' => $schema->number()
                ->description('Longitude in decimal degrees. Pair with latitude for an exact-coordinate search.'),

            'radius_miles' => $schema->number()
                ->description('Search radius in miles. Defaults to 10. Ignored if radius_km is provided or no coordinates are given.')
                ->default(10),

            'radius_km' => $schema->number()
                ->description('Search radius in kilometers. Takes precedence over radius_miles when both are provided. Ignored when no coordinates are provided.'),

            'weekdays' => $schema->array()
                ->items($schema->integer())
                ->description('Weekday filter. 1=Sunday, 2=Monday, ..., 7=Saturday. Example: [2,4,6] for Mon/Wed/Fri.'),

            'starts_after' => $schema->string()
                ->description('Earliest start time as 24h "HH:MM" (e.g. "18:00").'),

            'starts_before' => $schema->string()
                ->description('Latest start time as 24h "HH:MM" (e.g. "21:30").'),

            'format_ids' => $schema->array()
                ->items($schema->integer())
                ->description('BMLT format IDs to require. Use list_formats to discover IDs (e.g. Open, Closed, Speaker, Beginners).'),

            'venue_types' => $schema->array()
                ->items($schema->integer())
                ->description('Venue type filter. 1=In-person, 2=Virtual, 3=Hybrid.'),

            'service_body_ids' => $schema->array()
                ->items($schema->integer())
                ->description('Restrict to specific service bodies (regions/areas). Use list_service_bodies to discover IDs.'),

            'recursive_services' => $schema->boolean()
                ->description('When service_body_ids is set, also include their children. Default true.')
                ->default(true),

            'search_text' => $schema->string()
                ->description('Free-text search across meeting name, location, and notes.'),

            'language' => $schema->string()
                ->description('Language code for format names (e.g. "en", "es", "de", "fr").'),

            'page_size' => $schema->integer()
                ->description('Results per page. Omit to return all matches.'),

            'page' => $schema->integer()
                ->description('Page number (1-indexed). Used with page_size.')
                ->default(1),

            'data_format' => $schema->string()
                ->enum(['summary', 'full'])
                ->description('"summary" returns a curated subset of fields (default). "full" returns the raw BMLT response.')
                ->default('summary'),

            'root_server_url' => $schema->string()
                ->description('Override the configured BMLT root server URL. Must be allowlisted unless BMLT_ALLOW_ANY_ROOT=true.'),
        ];
    }

    private function applyTime(array &$params, string $prefix, string $hhmm): void
    {
        if ($hhmm === '' || ! preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $hhmm, $m)) {
            return;
        }
        $params["{$prefix}H"] = (int) $m[1];
        $params["{$prefix}M"] = (int) $m[2];
    }

    /**
     * @param  array<string, mixed>  $meeting
     * @return array<string, mixed>
     */
    private function summarize(array $meeting): array
    {
        $out = [];
        foreach (self::SUMMARY_FIELDS as $field) {
            if (array_key_exists($field, $meeting) && $meeting[$field] !== '' && $meeting[$field] !== null) {
                $out[$field] = $meeting[$field];
            }
        }

        return $out;
    }
}
