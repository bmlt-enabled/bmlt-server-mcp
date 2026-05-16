<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $tools = [
        ['name' => 'search_meetings', 'description' => 'Search meetings near an address or lat/lng with optional weekday, time, format, venue-type, and service-body filters.'],
        ['name' => 'get_meeting', 'description' => 'Fetch the full record for a single meeting by its BMLT id_bigint.'],
        ['name' => 'list_formats', 'description' => 'List meeting format codes (Open, Closed, Speaker, Beginners, language tags, …).'],
        ['name' => 'list_service_bodies', 'description' => 'List zones / regions / areas / groups so callers can map names to IDs.'],
        ['name' => 'get_server_info', 'description' => 'Capabilities, version, languages, and default coordinates for the configured root server.'],
        ['name' => 'list_root_servers', 'description' => 'Public BMLT root servers known to the BMLT aggregator.'],
    ];

    return response()->view('landing', [
        'mcpUrl' => url('/mcp'),
        'rootServer' => (string) config('bmlt.root_server_url'),
        'tools' => $tools,
    ]);
});
