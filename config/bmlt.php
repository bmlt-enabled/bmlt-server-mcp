<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default BMLT Root Server
    |--------------------------------------------------------------------------
    |
    | The base URL of the BMLT root server to query when a tool does not
    | specify a `root_server_url` override. Must include the path to the
    | main_server (e.g. https://latest.aws.bmlt.app/main_server).
    |
    */

    'root_server_url' => env('BMLT_ROOT_SERVER_URL', 'https://aggregator.bmltenabled.org/main_server'),

    /*
    |--------------------------------------------------------------------------
    | Allowed Root Servers
    |--------------------------------------------------------------------------
    |
    | Controls which root servers a caller may target via the optional
    | `root_server_url` tool argument. Use "*" to allow any URL (not
    | recommended in production — it permits SSRF-style queries to
    | arbitrary hosts). Otherwise provide a comma-separated allowlist of
    | base URLs.
    |
    */

    'allowed_roots' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('BMLT_ALLOWED_ROOTS', ''))
    ))),

    'allow_any_root' => env('BMLT_ALLOW_ANY_ROOT', false),

    /*
    |--------------------------------------------------------------------------
    | BMLT Aggregator
    |--------------------------------------------------------------------------
    |
    | The aggregator publishes a directory of public BMLT root servers and
    | is used by the `list_root_servers` tool. Override only if you run a
    | private aggregator.
    |
    */

    'aggregator_url' => env('BMLT_AGGREGATOR_URL', 'https://aggregator.bmltenabled.org/main_server'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => (int) env('BMLT_HTTP_TIMEOUT', 15),
        'user_agent' => env('BMLT_HTTP_USER_AGENT', 'bmlt-server-mcp/0.1 (+https://github.com/bmlt-enabled/bmlt-server-mcp)'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Access Log
    |--------------------------------------------------------------------------
    |
    | A per-request access log for /mcp, written to its own Monolog channel
    | so it stays separate from error logs. By default only metadata is
    | recorded (caller IP, User-Agent, JSON-RPC method, tool name, status,
    | latency). Set MCP_LOG_BODY=true to also log the raw request body when
    | debugging — turn it off again in normal operation, since the body
    | contains user search terms.
    |
    */

    'log_channel' => env('MCP_LOG_CHANNEL', 'mcp'),
    'log_body' => filter_var(env('MCP_LOG_BODY', false), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Geocoding
    |--------------------------------------------------------------------------
    |
    | Drivers:
    |   - "null"       : no server-side geocoding; address inputs are rejected
    |   - "nominatim"  : OpenStreetMap Nominatim (free, requires User-Agent)
    |   - "google"     : Google Geocoding API (requires API key)
    |
    */

    'geocoder' => [
        'default' => env('GEOCODER', 'nominatim'),

        'cache_ttl' => (int) env('GEOCODER_CACHE_TTL', 86400),

        'drivers' => [
            'nominatim' => [
                'endpoint' => env('NOMINATIM_ENDPOINT', 'https://nominatim.openstreetmap.org/search'),
                'user_agent' => env('NOMINATIM_USER_AGENT', env('BMLT_HTTP_USER_AGENT', 'bmlt-server-mcp/0.1')),
                'rate_limit_ms' => (int) env('NOMINATIM_RATE_LIMIT_MS', 1100),
            ],

            'google' => [
                'endpoint' => env('GOOGLE_GEOCODER_ENDPOINT', 'https://maps.googleapis.com/maps/api/geocode/json'),
                'api_key' => env('GOOGLE_GEOCODER_API_KEY'),
                'region' => env('GOOGLE_GEOCODER_REGION'),
                'language' => env('GOOGLE_GEOCODER_LANGUAGE'),
            ],
        ],
    ],

];
