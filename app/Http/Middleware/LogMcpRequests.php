<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight access logger for /mcp.
 *
 * Captures who called the server, which client they used, which JSON-RPC method
 * (and which tool, if tools/call), and how long it took. Tool arguments are
 * omitted by default to avoid logging user-supplied addresses/names; set
 * MCP_LOG_BODY=true to include them when debugging.
 */
class LogMcpRequests
{
    public function handle(Request $request, Closure $next)
    {
        $startedAt = microtime(true);

        $response = $next($request);

        $payload = $this->extractJsonRpc($request);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $context = [
            'ip' => $this->clientIp($request),
            'ua' => substr((string) $request->userAgent(), 0, 200),
            'origin' => $request->header('origin'),
            'method' => $payload['method'] ?? null,
            'tool' => $payload['tool'] ?? null,
            'status' => $response->getStatusCode(),
            'ms' => $durationMs,
        ];

        if (config('bmlt.log_body')) {
            $context['body'] = $payload['raw'];
        }

        Log::channel((string) config('bmlt.log_channel', 'mcp'))
            ->info('mcp', array_filter($context, fn ($v) => $v !== null && $v !== ''));

        return $response;
    }

    /**
     * Pull the JSON-RPC method and (if applicable) tool name without choking
     * on malformed bodies.
     *
     * @return array{method: ?string, tool: ?string, raw: array<mixed>}
     */
    private function extractJsonRpc(Request $request): array
    {
        $raw = $request->json()->all();
        $method = is_string($raw['method'] ?? null) ? $raw['method'] : null;
        $tool = null;
        if ($method === 'tools/call') {
            $name = $raw['params']['name'] ?? null;
            $tool = is_string($name) ? $name : null;
        }

        return ['method' => $method, 'tool' => $tool, 'raw' => $raw];
    }

    private function clientIp(Request $request): string
    {
        // Cloudflare / Hostinger LiteSpeed forwards the real client here.
        foreach (['cf-connecting-ip', 'x-forwarded-for'] as $header) {
            $value = $request->header($header);
            if (is_string($value) && $value !== '') {
                return trim(explode(',', $value)[0]);
            }
        }

        return (string) $request->ip();
    }
}
