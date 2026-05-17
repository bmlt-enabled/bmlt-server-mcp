<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tools Reference — BMLT MCP Server</title>
<meta name="description" content="Reference for every tool the BMLT MCP server exposes — parameters, defaults, and BMLT semantic-search-API mappings. For developers and AI agents integrating Narcotics Anonymous meeting search.">
<link rel="canonical" href="{{ url('/reference') }}">
<style>
    :root {
        color-scheme: light dark;
        --fg: #111827;
        --muted: #6b7280;
        --bg: #fafafa;
        --card: #ffffff;
        --border: #e5e7eb;
        --code-bg: #f3f4f6;
        --accent: #2563eb;
    }
    @media (prefers-color-scheme: dark) {
        :root {
            --fg: #e5e7eb;
            --muted: #9ca3af;
            --bg: #0b0f15;
            --card: #111827;
            --border: #1f2937;
            --code-bg: #1f2937;
            --accent: #60a5fa;
        }
    }
    * { box-sizing: border-box; }
    body {
        font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        max-width: 920px;
        margin: 0 auto;
        padding: 2.5rem 1.25rem 4rem;
        line-height: 1.6;
        color: var(--fg);
        background: var(--bg);
    }
    h1 { font-size: 1.8rem; margin: 0 0 .25rem; letter-spacing: -.01em; }
    h2 { font-size: 1.25rem; margin: 2.25rem 0 .5rem; letter-spacing: -.01em; }
    h3 { font-size: 1rem; margin: 1.5rem 0 .35rem; }
    .lead { color: var(--muted); margin: 0 0 1.5rem; }
    a { color: var(--accent); text-decoration: none; }
    a:hover { text-decoration: underline; }
    code {
        background: var(--code-bg);
        padding: 1px 6px;
        border-radius: 4px;
        font-size: .9em;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    }
    pre {
        background: var(--code-bg);
        border-radius: 8px;
        padding: .9rem 1.1rem;
        overflow-x: auto;
        font-size: .85rem;
        line-height: 1.45;
    }
    pre code { background: transparent; padding: 0; }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: .5rem 0 1.25rem;
        font-size: .9rem;
    }
    th, td {
        border-bottom: 1px solid var(--border);
        padding: .55rem .65rem;
        text-align: left;
        vertical-align: top;
    }
    th { font-weight: 600; color: var(--muted); font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; }
    td.name { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; white-space: nowrap; color: var(--accent); }
    td.type { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; color: var(--muted); white-space: nowrap; }
    .toc { background: var(--card); border: 1px solid var(--border); border-radius: 8px; padding: .9rem 1.1rem; margin: 1rem 0 2rem; }
    .toc ul { margin: .25rem 0 0; padding-left: 1.25rem; }
    .toc li { margin: .15rem 0; }
    .endpoint {
        display: inline-block;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: .35rem .75rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: .9rem;
    }
    .endpoint code { background: transparent; padding: 0; color: var(--accent); }
    footer { margin-top: 3rem; color: var(--muted); font-size: .85rem; }
</style>
</head>
<body>

<p><a href="{{ url('/') }}">&larr; back to home</a></p>

<h1>Tools Reference</h1>
<p class="lead">
    Every tool exposed by this MCP server, with parameters, types, defaults, and how each
    maps to the underlying <a href="https://bmlt.app/semantic/" target="_blank" rel="noopener">BMLT
    Semantic Search API</a>.
</p>

<h2>Endpoint</h2>
<p>
    <span class="endpoint"><code>{{ $mcpUrl }}</code></span> &nbsp;
    Protocol: MCP over HTTP (Streamable HTTP transport, JSON-RPC 2.0).
</p>

<h2>Default backend</h2>
<p>
    <span class="endpoint"><code>{{ $rootServer }}</code></span> &nbsp;
    The BMLT aggregator federates every public BMLT root server
    (<a href="https://raw.githubusercontent.com/bmlt-enabled/aggregator/refs/heads/main/serverList.json" target="_blank" rel="noopener">server list</a>),
    so searches cover NA meetings worldwide — approximately 85% of all NA meetings publish
    to one of these servers.
</p>

<div class="toc">
    <strong>Tools</strong>
    <ul>
        <li><a href="#search_meetings"><code>search_meetings</code></a> — geographic + filtered meeting search</li>
        <li><a href="#get_meeting"><code>get_meeting</code></a> — single meeting by ID</li>
        <li><a href="#list_formats"><code>list_formats</code></a> — meeting format codes (Open, Closed, Speaker, &hellip;)</li>
        <li><a href="#list_service_bodies"><code>list_service_bodies</code></a> — zones / regions / areas / groups</li>
        <li><a href="#get_server_info"><code>get_server_info</code></a> — server capabilities &amp; metadata</li>
        <li><a href="#list_root_servers"><code>list_root_servers</code></a> — every public BMLT root server</li>
    </ul>
</div>

<h2 id="search_meetings"><code>search_meetings</code></h2>
<p>
    Search NA meetings on the configured BMLT root server. Filter by location (address or
    lat/lng + radius), weekday, time, format, venue type, and service body. Returns a
    trimmed summary by default; pass <code>data_format=full</code> for the raw BMLT response.
</p>
<p>Read-only, idempotent.</p>

<table>
    <thead><tr><th>Parameter</th><th>Type</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td class="name">address</td><td class="type">string</td><td>—</td><td>Free-form address; geocoded server-side. Ignored if lat/lng are provided.</td></tr>
        <tr><td class="name">latitude</td><td class="type">number</td><td>—</td><td>Decimal degrees. Pair with longitude.</td></tr>
        <tr><td class="name">longitude</td><td class="type">number</td><td>—</td><td>Decimal degrees. Pair with latitude.</td></tr>
        <tr><td class="name">radius_miles</td><td class="type">number</td><td>10</td><td>Search radius. Ignored without coordinates.</td></tr>
        <tr><td class="name">weekdays</td><td class="type">int[]</td><td>—</td><td>1=Sun, 2=Mon, &hellip;, 7=Sat. e.g. <code>[2,4,6]</code>.</td></tr>
        <tr><td class="name">starts_after</td><td class="type">"HH:MM"</td><td>—</td><td>Earliest start time, 24-hour.</td></tr>
        <tr><td class="name">starts_before</td><td class="type">"HH:MM"</td><td>—</td><td>Latest start time, 24-hour.</td></tr>
        <tr><td class="name">format_ids</td><td class="type">int[]</td><td>—</td><td>BMLT format IDs to require. Discover with <code>list_formats</code>.</td></tr>
        <tr><td class="name">venue_types</td><td class="type">int[]</td><td>—</td><td>1=in-person, 2=virtual, 3=hybrid.</td></tr>
        <tr><td class="name">service_body_ids</td><td class="type">int[]</td><td>—</td><td>Restrict to regions/areas. Discover with <code>list_service_bodies</code>.</td></tr>
        <tr><td class="name">recursive_services</td><td class="type">boolean</td><td>true</td><td>Include child service bodies when filtering.</td></tr>
        <tr><td class="name">search_text</td><td class="type">string</td><td>—</td><td>Free-text search across name, location, notes.</td></tr>
        <tr><td class="name">language</td><td class="type">string</td><td>—</td><td>Format-name language: <code>en</code>, <code>es</code>, <code>de</code>, <code>fr</code>, &hellip;</td></tr>
        <tr><td class="name">page_size</td><td class="type">integer</td><td>—</td><td>Page size; omit to return all results.</td></tr>
        <tr><td class="name">page</td><td class="type">integer</td><td>1</td><td>1-indexed page number.</td></tr>
        <tr><td class="name">data_format</td><td class="type">"summary" | "full"</td><td>summary</td><td>Field set returned.</td></tr>
        <tr><td class="name">root_server_url</td><td class="type">string</td><td>—</td><td>Override the configured BMLT root server (allowlisted).</td></tr>
    </tbody>
</table>

<h3>BMLT API mapping</h3>
<p>
    Calls <code>GET {root}/client_interface/json/?switcher=GetSearchResults</code> with the
    parameters mapped as follows:
</p>
<table>
    <thead><tr><th>This tool</th><th>BMLT parameter</th></tr></thead>
    <tbody>
        <tr><td class="name">latitude</td><td class="name">lat_val</td></tr>
        <tr><td class="name">longitude</td><td class="name">long_val</td></tr>
        <tr><td class="name">radius_miles</td><td class="name">geo_width</td></tr>
        <tr><td class="name">weekdays</td><td class="name">weekdays[]</td></tr>
        <tr><td class="name">format_ids</td><td class="name">formats[]</td></tr>
        <tr><td class="name">venue_types</td><td class="name">venue_types[]</td></tr>
        <tr><td class="name">service_body_ids</td><td class="name">services[]</td></tr>
        <tr><td class="name">recursive_services</td><td class="name">recursive (1/0)</td></tr>
        <tr><td class="name">search_text</td><td class="name">SearchString</td></tr>
        <tr><td class="name">language</td><td class="name">lang_enum</td></tr>
        <tr><td class="name">starts_after</td><td class="name">StartsAfterH, StartsAfterM</td></tr>
        <tr><td class="name">starts_before</td><td class="name">StartsBeforeH, StartsBeforeM</td></tr>
        <tr><td class="name">page_size, page</td><td class="name">page_size, page_num</td></tr>
    </tbody>
</table>

<h3>Summary response fields</h3>
<p>With <code>data_format=summary</code> each meeting is trimmed to:</p>
<pre><code>id_bigint, meeting_name, weekday_tinyint, start_time, duration_time, time_zone,
location_text, location_street, location_municipality, location_province,
location_postal_code_1, latitude, longitude, formats, venue_type,
distance_in_miles, distance_in_km, virtual_meeting_link, phone_meeting_number,
comments, service_body_bigint</code></pre>

<h2 id="get_meeting"><code>get_meeting</code></h2>
<p>Fetch the full record for a single meeting by its BMLT <code>id_bigint</code>.</p>
<table>
    <thead><tr><th>Parameter</th><th>Type</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td class="name">id</td><td class="type">integer (required)</td><td>—</td><td>BMLT <code>id_bigint</code>.</td></tr>
        <tr><td class="name">root_server_url</td><td class="type">string</td><td>—</td><td>Override the configured root server.</td></tr>
    </tbody>
</table>
<p>Maps to <code>GetSearchResults&amp;meeting_ids[]={id}</code>.</p>

<h2 id="list_formats"><code>list_formats</code></h2>
<p>List meeting format codes (Open, Closed, Speaker, Beginners, language tags, &hellip;).</p>
<table>
    <thead><tr><th>Parameter</th><th>Type</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td class="name">language</td><td class="type">string</td><td>server default</td><td>Format-name language.</td></tr>
        <tr><td class="name">include_unused</td><td class="type">boolean</td><td>false</td><td>Include formats not assigned to any meeting.</td></tr>
        <tr><td class="name">format_ids</td><td class="type">int[]</td><td>—</td><td>Restrict to specific format IDs.</td></tr>
        <tr><td class="name">key_strings</td><td class="type">string[]</td><td>—</td><td>Restrict to short codes, e.g. <code>["O", "C", "SP"]</code>.</td></tr>
        <tr><td class="name">root_server_url</td><td class="type">string</td><td>—</td><td>Override the configured root server.</td></tr>
    </tbody>
</table>
<p>Maps to <code>GetFormats</code>.</p>

<h2 id="list_service_bodies"><code>list_service_bodies</code></h2>
<p>List zones, regions, areas, and groups, so callers can map names to IDs.</p>
<table>
    <thead><tr><th>Parameter</th><th>Type</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td class="name">service_body_ids</td><td class="type">int[]</td><td>—</td><td>Restrict to specific IDs.</td></tr>
        <tr><td class="name">include_children</td><td class="type">boolean</td><td>false</td><td>Include descendants of requested IDs.</td></tr>
        <tr><td class="name">include_parents</td><td class="type">boolean</td><td>false</td><td>Include ancestors of requested IDs.</td></tr>
        <tr><td class="name">root_server_url</td><td class="type">string</td><td>—</td><td>Override the configured root server.</td></tr>
    </tbody>
</table>
<p>Maps to <code>GetServiceBodies</code>.</p>

<h2 id="get_server_info"><code>get_server_info</code></h2>
<p>Capabilities, version, languages, and default coordinates for a BMLT root server.</p>
<table>
    <thead><tr><th>Parameter</th><th>Type</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td class="name">root_server_url</td><td class="type">string</td><td>—</td><td>Override the configured root server.</td></tr>
    </tbody>
</table>
<p>Maps to <code>GetServerInfo</code>.</p>

<h2 id="list_root_servers"><code>list_root_servers</code></h2>
<p>Public BMLT root servers known to the BMLT aggregator — the full list of regional BMLT installations worldwide.</p>
<table>
    <thead><tr><th>Parameter</th><th>Type</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td class="name">name_contains</td><td class="type">string</td><td>—</td><td>Case-insensitive substring filter on server name.</td></tr>
    </tbody>
</table>
<p>Source: <a href="https://raw.githubusercontent.com/bmlt-enabled/aggregator/refs/heads/main/serverList.json" target="_blank" rel="noopener">serverList.json</a>.</p>

<h2>Useful references</h2>
<ul>
    <li><a href="https://bmlt.app/semantic/" target="_blank" rel="noopener">BMLT Semantic Search API</a> — underlying HTTP API.</li>
    <li><a href="https://modelcontextprotocol.io" target="_blank" rel="noopener">Model Context Protocol</a> — open spec for AI tool connections.</li>
    <li><a href="https://github.com/bmlt-enabled/bmlt-server-mcp" target="_blank" rel="noopener">bmlt-enabled/bmlt-server-mcp</a> — this server's source.</li>
    <li><a href="https://github.com/bmlt-enabled/bmlt-skill" target="_blank" rel="noopener">bmlt-enabled/bmlt-skill</a> — companion Claude Skill with adjacent reference docs.</li>
</ul>

<footer>
    BMLT MCP Server &middot;
    <a href="{{ url('/') }}">home</a> &middot;
    <a href="https://github.com/bmlt-enabled/bmlt-server-mcp" target="_blank" rel="noopener">source</a>
</footer>

</body>
</html>
