<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesBmltClient;
use App\Services\Bmlt\BmltClientFactory;
use App\Services\Bmlt\BmltException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_service_bodies')]
#[Title('List service bodies (regions / areas)')]
#[Description(
    'List BMLT service bodies (zones, regions, areas, groups). Useful for translating '
    .'"Northern California Region" to a service body ID for use with search_meetings.'
)]
#[IsReadOnly]
#[IsIdempotent]
class ListServiceBodiesTool extends Tool
{
    use ResolvesBmltClient;

    public function handle(Request $request, BmltClientFactory $factory): Response
    {
        $client = $this->resolveClient($request, $factory);
        if ($client instanceof Response) {
            return $client;
        }

        $params = [];
        if ($ids = $request->get('service_body_ids')) {
            $params['services'] = (array) $ids;
        }
        if ($request->get('include_children')) {
            $params['recursive'] = 1;
        }
        if ($request->get('include_parents')) {
            $params['parents'] = 1;
        }

        try {
            $bodies = $client->getServiceBodies($params);
        } catch (BmltException $e) {
            return Response::error($e->getMessage());
        }

        return Response::json([
            'root_server' => $client->rootUrl(),
            'count' => count($bodies),
            'service_bodies' => $bodies,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'service_body_ids' => $schema->array()
                ->items($schema->integer())
                ->description('Restrict to specific service body IDs.'),

            'include_children' => $schema->boolean()
                ->description('Also include child service bodies of the requested IDs.')
                ->default(false),

            'include_parents' => $schema->boolean()
                ->description('Also include parent service bodies of the requested IDs.')
                ->default(false),

            'root_server_url' => $schema->string()
                ->description('Override the configured BMLT root server URL.'),
        ];
    }
}
