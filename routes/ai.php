<?php

use App\Http\Middleware\LogMcpRequests;
use App\Mcp\Servers\BmltServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', BmltServer::class)
    ->middleware(['throttle:60,1', LogMcpRequests::class]);

// laravel/mcp registers GET /mcp as a hard 405 (per spec, standalone SSE is
// optional). Override with a small JSON hint so browser GETs see something
// useful. Real MCP clients use POST and are unaffected.
Route::get('/mcp', static fn () => response()->json([
    'name' => 'bmlt-server-mcp',
    'transport' => 'streamable-http',
    'method' => 'POST',
    'hint' => 'Send JSON-RPC 2.0 requests via POST with Accept: application/json, text/event-stream',
    'docs' => url('/'),
]));
