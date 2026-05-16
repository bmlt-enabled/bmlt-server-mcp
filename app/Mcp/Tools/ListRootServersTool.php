<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_root_servers')]
#[Title('List known BMLT root servers')]
#[Description(
    'List public BMLT root servers known to the BMLT aggregator. Use the returned '
    . 'root_server_url values with other tools to query a specific server.'
)]
#[IsReadOnly]
#[IsIdempotent]
class ListRootServersTool extends Tool
{
    public function handle(Request $request, Config $config): Response
    {
        $aggregator = rtrim((string) $config->get('bmlt.aggregator_url'), '/');
        $endpoint = $aggregator . '/api/v1/rootservers';

        $response = Http::withHeaders([
            'User-Agent' => (string) $config->get('bmlt.http.user_agent'),
            'Accept' => 'application/json',
        ])->timeout((int) $config->get('bmlt.http.timeout', 15))->get($endpoint);

        if (! $response->successful()) {
            return Response::error("Aggregator returned HTTP {$response->status()} from {$endpoint}");
        }

        $rows = $response->json();
        if (! is_array($rows)) {
            return Response::error('Aggregator returned a non-list response.');
        }

        $filter = strtolower(trim((string) $request->get('name_contains', '')));
        if ($filter !== '') {
            $rows = array_values(array_filter(
                $rows,
                fn ($r) => str_contains(strtolower((string) ($r['name'] ?? '')), $filter)
            ));
        }

        $servers = array_map(static fn ($r) => [
            'id' => $r['id'] ?? null,
            'name' => $r['name'] ?? null,
            'root_server_url' => isset($r['url']) ? rtrim((string) $r['url'], '/') : null,
            'num_meetings' => $r['num_meetings'] ?? null,
            'server_info' => $r['server_info'] ?? null,
            'last_successful_import' => $r['last_successful_import'] ?? null,
        ], $rows);

        return Response::json([
            'aggregator' => $aggregator,
            'count' => count($servers),
            'servers' => $servers,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name_contains' => $schema->string()
                ->description('Case-insensitive substring filter applied to the server name.'),
        ];
    }
}
