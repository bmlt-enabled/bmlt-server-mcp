# bmlt-server-mcp

A **streamable HTTP** [Model Context Protocol](https://modelcontextprotocol.io) server that exposes [BMLT](https://bmlt.app) (Basic Meeting List Toolbox — the Narcotics Anonymous meeting directory) as a set of read-only tools that AI assistants like Claude and ChatGPT can call directly.

Unlike the [npm `bmlt-mcp-server`](https://www.npmjs.com/package/bmlt-mcp-server) (stdio-only, runs as a local subprocess), this server speaks MCP over HTTP and is meant to be **hosted once** and consumed remotely by any compatible client.

Built on **Laravel + PHP 8.2+** using the official [`laravel/mcp`](https://github.com/laravel/mcp) package.

---

## Tools

All tools are read-only and idempotent. All tools accept an optional `root_server_url` argument; if omitted, the configured `BMLT_ROOT_SERVER_URL` is used.

| Tool | What it does |
| --- | --- |
| `search_meetings` | Search meetings by address (geocoded server-side) or lat/lng + radius, filtered by weekday, time, format, venue type (in-person / virtual / hybrid), service body, and free text. Returns a curated summary by default (`data_format=full` for the raw BMLT response). |
| `get_meeting` | Fetch a single meeting by its BMLT `id_bigint`. |
| `list_formats` | List meeting format codes (Open, Closed, Speaker, Beginners, language tags, …) so callers can map names → IDs for `search_meetings`. |
| `list_service_bodies` | List zones / regions / areas / groups so callers can map names → IDs for `search_meetings`. |
| `get_server_info` | Capabilities, version, languages, and default coordinates for the configured root server. |
| `list_root_servers` | Public BMLT root servers known to the aggregator. Useful when the caller wants to switch roots. |

---

## Quick start

### Docker (recommended)

```bash
cp .env.example .env
docker compose up --build
```

The MCP endpoint is then live at `http://localhost:8080/mcp` over the [Streamable HTTP transport](https://modelcontextprotocol.io/specification/2024-11-05/basic/transports#streamable-http).

### Local PHP

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve     # http://localhost:8000/mcp
```

### Verify with the MCP Inspector

```bash
php artisan mcp:inspector
```

Then connect to `http://localhost:8000/mcp` (or `:8080` for Docker) and list tools.

---

## Configuration

All configuration lives in `.env` (see `.env.example`). The interesting bits:

| Variable | Purpose |
| --- | --- |
| `BMLT_ROOT_SERVER_URL` | **Required.** Default BMLT root server, including `/main_server` path. |
| `BMLT_ALLOWED_ROOTS` | Comma-separated allowlist for the optional `root_server_url` tool argument. The default root is always implicitly allowed. |
| `BMLT_ALLOW_ANY_ROOT` | `true` allows any URL via `root_server_url` — **not recommended** in production (enables SSRF-style queries). Off by default. |
| `BMLT_AGGREGATOR_URL` | Aggregator queried by `list_root_servers`. Defaults to the public aggregator. |
| `GEOCODER` | `nominatim` (default), `google`, or `null`. `null` rejects address inputs and requires lat/lng. |
| `NOMINATIM_USER_AGENT` | Identifies your deployment to OSM — required by Nominatim's ToS. Always set this in production. |
| `GOOGLE_GEOCODER_API_KEY` | Required when `GEOCODER=google`. |

### Why an allowlist?

The optional `root_server_url` tool argument lets a single deployment serve any BMLT root, but accepting arbitrary URLs would let callers turn the server into an SSRF probe against your network. The default is a strict allowlist (the configured default root, plus anything you add to `BMLT_ALLOWED_ROOTS`). Set `BMLT_ALLOW_ANY_ROOT=true` only when the server is isolated from anything sensitive.

---

## Connecting from Claude / ChatGPT

### Claude Desktop / Claude.ai (via mcp-remote)

```jsonc
{
  "mcpServers": {
    "bmlt": {
      "command": "npx",
      "args": ["mcp-remote", "https://your-host.example.com/mcp"]
    }
  }
}
```

### ChatGPT (custom GPT — MCP action)

Point the action at `https://your-host.example.com/mcp` with whatever auth middleware you've added.

---

## Adding authentication

The default config has no auth — anyone who can reach `/mcp` can call the tools. To gate it:

```php
// routes/ai.php
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();                          // OAuth 2.1 (Laravel Passport)
Mcp::web('/mcp', BmltServer::class)
    ->middleware('auth:api');
```

Or add a simple bearer-token check via custom middleware. See the [Laravel MCP docs](https://laravel.com/docs/mcp) for the full options.

---

## Architecture

```
app/
├── Mcp/
│   ├── Servers/BmltServer.php           # Registers the 6 tools
│   └── Tools/
│       ├── SearchMeetingsTool.php
│       ├── GetMeetingTool.php
│       ├── ListFormatsTool.php
│       ├── ListServiceBodiesTool.php
│       ├── GetServerInfoTool.php
│       ├── ListRootServersTool.php
│       └── Concerns/ResolvesBmltClient.php
├── Services/
│   ├── Bmlt/
│   │   ├── BmltClient.php               # Wraps client_interface/json
│   │   ├── BmltClientFactory.php        # Allowlist enforcement
│   │   └── BmltException.php
│   └── Geocoding/
│       ├── Geocoder.php                 # Interface
│       ├── GeocoderManager.php          # Driver resolver
│       ├── NominatimGeocoder.php        # OSM (rate-limited, cached)
│       ├── GoogleGeocoder.php           # Google Geocoding API
│       ├── NullGeocoder.php             # Disabled
│       ├── GeocodingResult.php
│       └── GeocodingException.php
└── Providers/BmltServiceProvider.php

config/bmlt.php                          # All knobs
routes/ai.php                            # Mcp::web('/mcp', BmltServer::class)
```

---

## License

MIT
