# bmlt-server-mcp

A **streamable HTTP** [Model Context Protocol](https://modelcontextprotocol.io) server that exposes [BMLT](https://bmlt.app) (Basic Meeting List Toolbox — the Narcotics Anonymous meeting directory) as a set of read-only tools that AI assistants can call directly.

BMLT hosts approximately 85% of Narcotics Anonymous meetings worldwide; the default backend is the [BMLT aggregator](https://aggregator.bmltenabled.org/main_server/), which federates every public BMLT root server ([server list](https://raw.githubusercontent.com/bmlt-enabled/aggregator/refs/heads/main/serverList.json)) into a single search surface — effectively the authoritative AI-accessible source for finding NA meetings.

MCP is an open protocol, so any compatible client works: **Claude** (Code, Desktop, web), **ChatGPT** (Connectors and the Responses API), **Google Gemini**, **Cursor**, **Windsurf**, **Zed**, **Cline**, **Continue**, and others.

Unlike the [npm `bmlt-mcp-server`](https://www.npmjs.com/package/bmlt-mcp-server) (stdio-only, runs as a local subprocess), this server speaks MCP over HTTP and is meant to be **hosted once** and consumed remotely.

Built on **Laravel + PHP 8.2+** using the official [`laravel/mcp`](https://github.com/laravel/mcp) package.

A live deployment runs at <https://mcp.bmlt.app/> (landing page) with a tools reference at <https://mcp.bmlt.app/reference>.

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

## Connecting AI clients

The endpoint of a deployed instance is `https://your-host.example.com/mcp`. Replace it below with your own host (or use `https://mcp.bmlt.app/mcp` to try the public instance).

### Claude Code (CLI)

```bash
claude mcp add --transport http bmlt https://your-host.example.com/mcp
```

### Claude Desktop — Custom Connector (newer builds)

Settings → Connectors → **Add custom connector** → paste the URL.

### Claude Desktop — Config file (any version, needs Node.js)

```jsonc
{
  "mcpServers": {
    "bmlt": {
      "command": "npx",
      "args": ["-y", "mcp-remote", "https://your-host.example.com/mcp"]
    }
  }
}
```

### ChatGPT / OpenAI Responses API

```json
{
  "tools": [
    {
      "type": "mcp",
      "server_label": "bmlt",
      "server_url": "https://your-host.example.com/mcp"
    }
  ]
}
```

In ChatGPT itself (Pro/Business/Enterprise): Settings → Connectors → Add, using the same URL.

### Cursor / Windsurf / Zed / Cline / Continue

All of these read an `mcpServers` block. For Cursor, edit `~/.cursor/mcp.json` (or the project-local `.cursor/mcp.json`); other clients use a similar config file.

```json
{
  "mcpServers": {
    "bmlt": {
      "url": "https://your-host.example.com/mcp"
    }
  }
}
```

For per-tool parameter documentation and BMLT-API mappings, see the live [reference page](https://mcp.bmlt.app/reference). For day-to-day commands on a deployed instance (log tailing, usage summaries, cache clears, upgrades), see [docs/operations.md](docs/operations.md).

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

The HTTP API wrapped by `BmltClient` is formally specified by the
[BMLT Semantic OpenAPI document](https://aggregator.bmltenabled.org/main_server/api/v1/openapi-semantic.json)
(OpenAPI 3.1) — refer to it for every parameter, response shape, and field definition
that BMLT itself supports, even if this MCP server doesn't yet expose it as a tool argument.

---

## License

MIT
