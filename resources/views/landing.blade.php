<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>BMLT MCP — Connect Claude</title>
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
        --pill-bg: #10b98122;
        --pill-fg: #047857;
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
            --pill-fg: #6ee7b7;
        }
    }
    * { box-sizing: border-box; }
    body {
        font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        max-width: 820px;
        margin: 0 auto;
        padding: 2.5rem 1.25rem 4rem;
        line-height: 1.6;
        color: var(--fg);
        background: var(--bg);
    }
    h1 { font-size: 1.8rem; margin: 0 0 .25rem; letter-spacing: -.01em; }
    h2 { font-size: 1.15rem; margin: 2.25rem 0 .75rem; letter-spacing: -.01em; }
    h3 { font-size: 1rem; margin: 1.5rem 0 .5rem; }
    p { margin: .5rem 0; }
    a { color: var(--accent); text-decoration: none; }
    a:hover { text-decoration: underline; }
    .lead { color: var(--muted); margin: 0 0 1.5rem; font-size: 1.02rem; }
    .pill {
        display: inline-block;
        background: var(--pill-bg);
        color: var(--pill-fg);
        padding: 2px 10px;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 600;
        margin-left: .5rem;
        vertical-align: middle;
        letter-spacing: .02em;
    }
    .endpoint {
        display: flex;
        align-items: center;
        gap: .5rem;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: .75rem 1rem;
        margin: .5rem 0 1.5rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: .92rem;
        word-break: break-all;
    }
    .endpoint code { background: transparent; padding: 0; color: var(--accent); }
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
        margin: .75rem 0;
    }
    pre code { background: transparent; padding: 0; }
    ol { padding-left: 1.25rem; }
    ol li { margin: .35rem 0; }
    ul.tools { list-style: none; padding: 0; margin: 0; }
    ul.tools li {
        padding: .65rem 0;
        border-bottom: 1px solid var(--border);
    }
    ul.tools li:last-child { border-bottom: 0; }
    .tname {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 600;
        color: var(--accent);
    }
    .tdesc { color: var(--muted); font-size: .92rem; margin-top: .15rem; }
    .examples { padding-left: 1.25rem; }
    .examples li { margin: .4rem 0; color: var(--muted); }
    .examples li::marker { color: var(--muted); }
    .examples em { color: var(--fg); font-style: normal; }
    hr {
        border: 0;
        border-top: 1px solid var(--border);
        margin: 2.5rem 0 1.5rem;
    }
    footer {
        margin-top: 2rem;
        color: var(--muted);
        font-size: .85rem;
    }
    .callout {
        background: var(--card);
        border: 1px solid var(--border);
        border-left: 3px solid var(--accent);
        border-radius: 6px;
        padding: .65rem 1rem;
        font-size: .9rem;
        color: var(--muted);
        margin: 1rem 0;
    }
</style>
</head>
<body>

<h1>BMLT MCP <span class="pill">alive</span></h1>
<p class="lead">Talk to BMLT meeting data directly from Claude.</p>

<h2>What this is</h2>
<p>
    This site exposes a read-only <a href="https://modelcontextprotocol.io" target="_blank" rel="noopener">MCP</a>
    (Model Context Protocol) server that lets Claude query a
    <a href="https://bmlt.app" target="_blank" rel="noopener">BMLT</a> root server
    — the directory of Narcotics Anonymous meetings —
    directly. Once connected, you can ask things like
    <em>"find an open NA meeting tonight near 1600 Pennsylvania Ave"</em> or
    <em>"what virtual speaker meetings happen on Sunday mornings?"</em>
    and Claude will answer using live data from this BMLT root server.
</p>

<h3>MCP endpoint</h3>
<div class="endpoint"><code>{{ $mcpUrl }}</code></div>

<h3>Default BMLT root server</h3>
<div class="endpoint"><code>{{ $rootServer }}</code></div>

<h3>Available tools (all read-only)</h3>
<ul class="tools">
    @foreach ($tools as $t)
        <li>
            <div class="tname">{{ $t['name'] }}</div>
            <div class="tdesc">{{ $t['description'] }}</div>
        </li>
    @endforeach
</ul>

<hr>

<h2>Option 1: Custom Connector (recommended)</h2>
<p>
    Newer versions of Claude Desktop can connect to remote MCP servers natively
    — no Node.js required.
</p>
<ol>
    <li>Open Claude Desktop.</li>
    <li>Go to <strong>Settings → Connectors</strong> (macOS: Claude menu → Settings; Windows: File → Settings).</li>
    <li>Scroll to the bottom and click <strong>Add custom connector</strong>.</li>
    <li>
        Fill in the dialog:
        <ul>
            <li><strong>Name:</strong> BMLT Meetings</li>
            <li><strong>Remote MCP server URL:</strong> <code>{{ $mcpUrl }}</code></li>
        </ul>
    </li>
    <li>Click <strong>Add</strong>, then start a new chat.</li>
</ol>
<div class="callout">
    If you don't see <strong>Add custom connector</strong>, your Claude Desktop is older than the feature requires.
    Update it, or use Option&nbsp;2.
</div>

<h2>Option 2: Config file</h2>
<p>Works on every Claude Desktop version. Requires Node.js.</p>
<ol>
    <li>
        Open Claude Desktop's config file:
        <ul>
            <li><strong>macOS:</strong> <code>~/Library/Application Support/Claude/claude_desktop_config.json</code></li>
            <li><strong>Windows:</strong> <code>%APPDATA%\Claude\claude_desktop_config.json</code></li>
        </ul>
        The fastest way: <strong>Settings → Developer → Edit Config</strong>.
    </li>
    <li>Add (or merge) the <code>mcpServers</code> entry below.</li>
    <li>Save and fully quit/reopen Claude Desktop.</li>
</ol>
<pre><code>{
  "mcpServers": {
    "bmlt": {
      "command": "npx",
      "args": [
        "-y",
        "mcp-remote",
        "{{ $mcpUrl }}"
      ]
    }
  }
}</code></pre>

<h2>Bonus: Claude Code (CLI)</h2>
<p>If you use Claude Code, native HTTP transport is built in:</p>
<pre><code>claude mcp add --transport http bmlt {{ $mcpUrl }}</code></pre>
<p>Then run <code>/mcp</code> inside a Claude Code session to confirm the connection.</p>

<h2>Try it out</h2>
<p>Once connected, ask Claude things like:</p>
<ul class="examples">
    <li><em>"Find me an open NA meeting tomorrow night within 5 miles of 1600 Pennsylvania Ave."</em></li>
    <li><em>"What Spanish-language meetings happen on Sunday mornings in Los Angeles?"</em></li>
    <li><em>"List all virtual speaker meetings in my area this weekend."</em></li>
    <li><em>"What service bodies fall under the Northern California Region?"</em></li>
    <li><em>"Show me details for meeting id 12345."</em></li>
</ul>

<footer>
    BMLT MCP Server &middot;
    <a href="https://github.com/bmlt-enabled/bmlt-server-mcp" target="_blank" rel="noopener">source</a>
    &middot; built on <a href="https://github.com/laravel/mcp" target="_blank" rel="noopener">laravel/mcp</a>
</footer>

</body>
</html>
