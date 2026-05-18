# Operations

Day-to-day commands for running, observing, and maintaining a deployed
bmlt-server-mcp instance. Most commands assume you're SSH'd into the server and
`cd`'d into the install directory (e.g. `~/domains/mcp.bmlt.app/public_html` on
Hostinger).

If your shared host's default PHP CLI is older than 8.3, substitute `php` with
the explicit binary (e.g. `php8.3`).

## Watch live traffic

The MCP access log is rotated daily.

```bash
tail -f storage/logs/mcp-$(date +%Y-%m-%d).log
```

For just `tools/call` lines (the interesting ones):

```bash
tail -f storage/logs/mcp-$(date +%Y-%m-%d).log | grep '"tools/call"'
```

## Usage summaries

Each line is JSON-LD-ish at the end of the Monolog prefix; the grep
one-liners below avoid needing `jq` but `jq` versions are also shown for
richer queries.

### Top clients (User-Agent)

```bash
grep -oE '"ua":"[^"]*"' storage/logs/mcp-*.log \
  | sort | uniq -c | sort -rn | head -20
```

Tells you which AI assistants are connecting (Claude, ChatGPT, Cursor, etc.).

### Top tools called

```bash
grep -oE '"tool":"[^"]*"' storage/logs/mcp-*.log \
  | sort | uniq -c | sort -rn
```

### Top callers (IP)

```bash
grep -oE '"ip":"[^"]*"' storage/logs/mcp-*.log \
  | sort | uniq -c | sort -rn | head -20
```

Useful for spotting noisy/abusive clients before they hit the rate limit.

### JSON-RPC method breakdown

```bash
grep -oE '"method":"[^"]*"' storage/logs/mcp-*.log \
  | sort | uniq -c | sort -rn
```

A healthy ratio is roughly `initialize : tools/list : tools/call` ≈ 1 : 1 : many.
Lots of `initialize` with no `tools/call` usually means clients are connecting
but not actually using the server.

### Requests per day

```bash
for f in storage/logs/mcp-*.log; do
  printf '%s  %d\n' "$(basename "$f" .log | cut -d- -f2-)" "$(wc -l < "$f")"
done
```

### Non-2xx responses (errors)

```bash
grep -E '"status":[^2]' storage/logs/mcp-*.log | tail -50
```

### Slow requests (>1s)

```bash
grep -E '"ms":[1-9][0-9]{3,}' storage/logs/mcp-*.log | tail -50
```

### With `jq` (if installed)

If `jq` is available the queries get easier and more flexible. Each log line is
`[time] env.LEVEL: mcp <json-context>`; strip the prefix with `sed` first.

```bash
cat storage/logs/mcp-*.log \
  | sed -E 's/^[^{]*//' \
  | jq -r '"\(.tool // .method // "?")\t\(.ua)\t\(.ip)"' \
  | sort | uniq -c | sort -rn | head -20
```

Avg latency per tool:

```bash
cat storage/logs/mcp-*.log | sed -E 's/^[^{]*//' \
  | jq -r 'select(.tool) | "\(.tool)\t\(.ms)"' \
  | awk -F'\t' '{s[$1]+=$2; c[$1]++} END {for (t in s) printf "%-30s  avg %dms  n=%d\n", t, s[t]/c[t], c[t]}' \
  | sort -k3 -rn
```

## Enable verbose body logging (debug only)

Off by default to keep user search terms (addresses, names) out of logs.
Temporarily enable:

```bash
# add or update in .env:
MCP_LOG_BODY=true

php artisan config:clear
```

Turn it back off when done. Body logging captures the **full** JSON-RPC body,
including user-supplied addresses and search strings.

## Clear Laravel caches

After a code/view/config change (or pulling a new release):

```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

If a Blade syntax error is sticking around even after edits, also nuke the
compiled views directly:

```bash
rm -f storage/framework/views/*.php
```

## Switch the default BMLT root server

Edit `.env`:

```env
BMLT_ROOT_SERVER_URL=https://example.bmlt.org/main_server
```

Then `php artisan config:clear`. The aggregator
(`https://aggregator.bmltenabled.org/main_server`) is the default and covers
~85% of NA meetings worldwide; only narrow it if you specifically want to scope
this deployment to one region.

## Allow other root servers per request

Add a comma-separated allowlist so callers may pass `root_server_url` as a
tool argument:

```env
BMLT_ALLOWED_ROOTS=https://bmlt.sezf.org/main_server,https://bmltwf.bmltenabled.org/main_server
```

Never set `BMLT_ALLOW_ANY_ROOT=true` on a production deployment — it turns the
server into an SSRF probe.

## Rotate / clean up logs

Daily rotation is automatic (`MCP_LOG_DAYS=30` by default). To force a
purge:

```bash
find storage/logs -name 'mcp-*.log' -mtime +30 -delete
find storage/logs -name 'laravel-*.log' -mtime +14 -delete
```

## Inspect the live MCP endpoint

The server responds to a JSON-RPC `tools/list` from any HTTP client:

```bash
curl -s -X POST https://mcp.bmlt.app/mcp \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json, text/event-stream' \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}' \
  | sed -E 's/^[^{]*//' | jq '.result.tools[].name'
```

Or use the bundled MCP Inspector locally:

```bash
php artisan mcp:inspector
# then point it at https://mcp.bmlt.app/mcp
```

## Deploy a new release

If the repo is checked out as a git working tree:

```bash
git fetch --tags
git reset --hard v0.X.Y
php artisan view:clear
php artisan config:clear
```

If you deployed via release zip:

```bash
curl -L -o bmlt.zip https://github.com/bmlt-enabled/bmlt-server-mcp/releases/download/v0.X.Y/bmlt-server-mcp-v0.X.Y.zip
unzip -o bmlt.zip
rm bmlt.zip
php artisan view:clear && php artisan config:clear
```

`.env`, `storage/`, and `vendor/` are unaffected in both flows.

## Health check

Lightweight liveness:

```bash
curl -fsS https://mcp.bmlt.app/ -o /dev/null && echo OK || echo DOWN
```

Functional (does the MCP endpoint respond)?

```bash
curl -fsS -X POST https://mcp.bmlt.app/mcp \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json, text/event-stream' \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}' >/dev/null \
  && echo OK || echo DOWN
```

Either is fine for a cron-driven monitor.
