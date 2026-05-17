<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Find Narcotics Anonymous Meetings — BMLT MCP Server for AI Assistants</title>
<meta name="description" content="Official BMLT-powered MCP server. Connect Claude, ChatGPT, Gemini, Cursor and other AI assistants to the worldwide Narcotics Anonymous meeting directory — the same dataset that powers most NA region and area websites.">
<meta name="keywords" content="Narcotics Anonymous meeting finder, find NA meetings near me, NA meeting search, NA meetings tonight, BMLT, MCP server, Model Context Protocol, NA meeting directory, virtual NA meetings, online NA meetings">
<link rel="canonical" href="{{ url('/') }}">
<meta property="og:type" content="website">
<meta property="og:title" content="Find Narcotics Anonymous Meetings — BMLT MCP Server for AI Assistants">
<meta property="og:description" content="Connect Claude, ChatGPT, Gemini, and other AI assistants to the official BMLT directory of worldwide Narcotics Anonymous meetings.">
<meta property="og:url" content="{{ url('/') }}">
<meta property="og:site_name" content="BMLT MCP">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Find Narcotics Anonymous Meetings — BMLT MCP Server">
<meta name="twitter:description" content="Official BMLT MCP server. Lets AI assistants search the worldwide NA meeting directory.">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "BMLT MCP — NA Meeting Finder for AI Assistants",
  "url": "{{ url('/') }}",
  "description": "Official BMLT-powered MCP server exposing the worldwide Narcotics Anonymous meeting directory to AI assistants.",
  "publisher": {
    "@type": "Organization",
    "name": "BMLT (Basic Meeting List Toolbox)",
    "url": "https://bmlt.app"
  },
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "{{ url('/') }}?q={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "BMLT MCP Server",
  "applicationCategory": "DeveloperApplication",
  "operatingSystem": "Any",
  "description": "Read-only Model Context Protocol (MCP) server exposing the worldwide BMLT Narcotics Anonymous meeting directory to AI assistants such as Claude, ChatGPT, Gemini, Cursor, Windsurf, Zed, Cline, and Continue.",
  "url": "{{ url('/') }}",
  "downloadUrl": "https://github.com/bmlt-enabled/bmlt-server-mcp",
  "softwareHelp": "{{ url('/reference') }}",
  "offers": { "@type": "Offer", "price": "0", "priceCurrency": "USD" },
  "author": {
    "@type": "Organization",
    "name": "BMLT (Basic Meeting List Toolbox)",
    "url": "https://bmlt.app"
  }
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How do I find Narcotics Anonymous meetings near me?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ask any MCP-connected AI assistant (Claude, ChatGPT, Gemini, Cursor, and others) something like 'find an NA meeting near 1600 Pennsylvania Ave tonight' or 'what virtual NA speaker meetings happen Sunday mornings?'. The assistant calls this server, which queries the BMLT aggregator and returns live meeting data from the worldwide Narcotics Anonymous directory."
      }
    },
    {
      "@type": "Question",
      "name": "Is this the official Narcotics Anonymous meeting list?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "This server queries BMLT (Basic Meeting List Toolbox), the open-source platform that hosts approximately 85% of Narcotics Anonymous meetings worldwide. NA service bodies — regions, areas, and groups — publish their meeting data to BMLT root servers, and the BMLT aggregator federates every public root server into a single search surface. This is the same authoritative dataset that powers most NA region and area websites."
      }
    },
    {
      "@type": "Question",
      "name": "Which AI assistants can use this server?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Any client that supports the Model Context Protocol (MCP) over HTTP — including Claude (Code, Desktop, web), ChatGPT (Connectors and the Responses API), Google Gemini, Cursor, Windsurf, Zed, Cline, Continue, and others."
      }
    },
    {
      "@type": "Question",
      "name": "What is MCP (Model Context Protocol)?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "MCP is an open standard for connecting AI assistants to external data sources and tools. It is vendor-neutral — published by Anthropic but adopted by OpenAI, Google, and the wider ecosystem."
      }
    },
    {
      "@type": "Question",
      "name": "Can I find virtual or online NA meetings?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. Filter by venue_type (1=in-person, 2=virtual, 3=hybrid) or just ask the assistant for 'virtual NA meetings' or 'online NA meetings tonight'. Virtual meeting links and phone numbers are returned when available."
      }
    },
    {
      "@type": "Question",
      "name": "Is this server free to use?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes — free and open-source. Source code is on GitHub at bmlt-enabled/bmlt-server-mcp."
      }
    }
  ]
}
</script>
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
    .header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }
    .header .gh {
        color: var(--muted);
        display: inline-flex;
        align-items: center;
        padding: .35rem;
        border-radius: 6px;
        transition: color .15s;
    }
    .header .gh:hover { color: var(--fg); text-decoration: none; }
    .header .gh svg { display: block; }
    .clients {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        margin: .25rem 0 1.25rem;
    }
    .clients span {
        background: var(--code-bg);
        color: var(--muted);
        padding: 2px 9px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 500;
    }
</style>
</head>
<body>

<div class="header">
    <div>
        <h1>Find Narcotics Anonymous Meetings <span class="pill">live</span></h1>
        <p class="lead">The authoritative NA meeting directory for AI assistants — Claude, ChatGPT, Gemini, Cursor, and any MCP-compatible client.</p>
    </div>
    <a class="gh" href="https://github.com/bmlt-enabled/bmlt-server-mcp" target="_blank" rel="noopener" aria-label="View source on GitHub" title="View source on GitHub">
        <svg height="28" width="28" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
        </svg>
    </a>
</div>
<div class="clients">
    <span>Claude</span>
    <span>ChatGPT</span>
    <span>Gemini</span>
    <span>Cursor</span>
    <span>Windsurf</span>
    <span>Zed</span>
    <span>Cline</span>
    <span>Continue</span>
</div>

<h2>The official source for AI-powered NA meeting search</h2>
<p>
    This is a read-only <a href="https://modelcontextprotocol.io" target="_blank" rel="noopener">MCP</a>
    (Model Context Protocol) server that connects any AI assistant to the worldwide
    Narcotics Anonymous meeting directory. Ask in plain English —
    <em>"find an open NA meeting tonight near 1600 Pennsylvania Ave"</em>,
    <em>"what virtual NA speaker meetings happen Sunday mornings?"</em>, or
    <em>"Spanish-language NA meetings in Los Angeles"</em> — and your AI answers with live data.
</p>

<h3>Same dataset most NA websites already use</h3>
<p>
    Behind the scenes this calls <a href="https://bmlt.app" target="_blank" rel="noopener">BMLT</a>
    (the Basic Meeting List Toolbox), the open-source platform that hosts approximately
    <strong>85% of Narcotics Anonymous meetings worldwide</strong>.
    NA regions, areas, and groups publish their meeting lists to BMLT root servers, and the
    <a href="https://aggregator.bmltenabled.org/main_server/" target="_blank" rel="noopener">BMLT aggregator</a>
    federates every public root server
    (<a href="https://raw.githubusercontent.com/bmlt-enabled/aggregator/refs/heads/main/serverList.json" target="_blank" rel="noopener">full list</a>)
    into a single search surface — effectively one giant meeting list covering NA worldwide.
    It is the same authoritative dataset that powers most NA region and area websites today.
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

<h2>Claude Desktop — Custom Connector (recommended)</h2>
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
    Update it, or use the config-file method below.
</div>

<h2>Claude Desktop — Config file</h2>
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

<h2>Claude Code (CLI)</h2>
<p>Native HTTP transport — one command:</p>
<pre><code>claude mcp add --transport http bmlt {{ $mcpUrl }}</code></pre>
<p>Then run <code>/mcp</code> inside a Claude Code session to confirm the connection.</p>

<h2>ChatGPT / OpenAI Responses API</h2>
<p>
    Add it as a tool in any Responses API call. In ChatGPT itself (Pro/Business/Enterprise),
    add it under <strong>Settings → Connectors → Add</strong> using the endpoint above.
</p>
<pre><code>{
  "model": "gpt-5",
  "tools": [
    {
      "type": "mcp",
      "server_label": "bmlt",
      "server_url": "{{ $mcpUrl }}"
    }
  ],
  "input": "Find an open NA meeting tonight near Boston."
}</code></pre>

<h2>Cursor / Windsurf / Zed / Cline / Continue</h2>
<p>
    All read an <code>mcpServers</code> block. For Cursor, edit
    <code>~/.cursor/mcp.json</code> (or the project-local <code>.cursor/mcp.json</code>);
    other clients use a similar file in their config directory.
</p>
<pre><code>{
  "mcpServers": {
    "bmlt": {
      "url": "{{ $mcpUrl }}"
    }
  }
}</code></pre>

<h2>Anything else</h2>
<p>
    Any MCP client that supports <strong>HTTP transport</strong> can connect.
    Point it at:
</p>
<div class="endpoint"><code>{{ $mcpUrl }}</code></div>

<h2>Try it out</h2>
<p>Once connected, ask Claude things like:</p>
<ul class="examples">
    <li><em>"Find me an open NA meeting tomorrow night within 5 miles of 1600 Pennsylvania Ave."</em></li>
    <li><em>"What Spanish-language meetings happen on Sunday mornings in Los Angeles?"</em></li>
    <li><em>"List all virtual speaker meetings in my area this weekend."</em></li>
    <li><em>"What service bodies fall under the Northern California Region?"</em></li>
    <li><em>"Show me details for meeting id 12345."</em></li>
</ul>

<h2>Frequently asked questions</h2>

<h3>How do I find Narcotics Anonymous meetings near me?</h3>
<p>
    Connect any MCP-compatible AI assistant using the instructions above, then ask in plain
    English — e.g. <em>"find an NA meeting near 1600 Pennsylvania Ave tonight"</em> or
    <em>"virtual NA speaker meetings on Sunday mornings"</em>. The assistant calls this server,
    which queries the BMLT aggregator and returns live data from the worldwide NA meeting list.
</p>

<h3>Is this the official Narcotics Anonymous meeting list?</h3>
<p>
    This server queries <a href="https://bmlt.app" target="_blank" rel="noopener">BMLT</a>,
    the open-source meeting-list platform that hosts roughly 85% of NA meetings worldwide.
    The default backend is the BMLT aggregator, which federates every public BMLT root server
    used by NA regions and areas. It is the same dataset that powers most NA region and area
    websites today.
</p>

<h3>Which AI assistants work with this?</h3>
<p>
    Any client that supports the Model Context Protocol over HTTP — Claude (Code, Desktop, web),
    ChatGPT (Connectors and the Responses API), Google Gemini, Cursor, Windsurf, Zed, Cline,
    Continue, and others. MCP is an open standard, not Claude-specific.
</p>

<h3>Can I find virtual or online NA meetings?</h3>
<p>
    Yes. Ask for <em>"virtual NA meetings"</em>, <em>"online NA meetings tonight"</em>, or filter
    by venue type (1=in-person, 2=virtual, 3=hybrid). Virtual meeting links and phone numbers are
    returned when the originating BMLT root server publishes them.
</p>

<h3>Is this server free?</h3>
<p>
    Yes — free and open-source under
    <a href="https://github.com/bmlt-enabled/bmlt-server-mcp" target="_blank" rel="noopener">bmlt-enabled/bmlt-server-mcp</a>.
    No account or API key required.
</p>

<h3>Where's the technical reference?</h3>
<p>
    See the <a href="{{ url('/reference') }}">tools reference</a> for every parameter and its
    BMLT-API mapping, or the <a href="https://github.com/bmlt-enabled/bmlt-server-mcp" target="_blank" rel="noopener">repository</a>
    for source and deployment notes.
</p>

<footer>
    BMLT MCP Server &middot;
    <a href="https://github.com/bmlt-enabled/bmlt-server-mcp" target="_blank" rel="noopener">source</a>
    &middot; <a href="{{ url('/reference') }}">tools reference</a>
    &middot; built on <a href="https://github.com/laravel/mcp" target="_blank" rel="noopener">laravel/mcp</a>
</footer>

</body>
</html>
