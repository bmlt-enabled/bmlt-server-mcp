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

#[Name('get_server_info')]
#[Title('Get BMLT root server info')]
#[Description('Return capabilities, version, languages, and default coordinates for a BMLT root server.')]
#[IsReadOnly]
#[IsIdempotent]
class GetServerInfoTool extends Tool
{
    use ResolvesBmltClient;

    public function handle(Request $request, BmltClientFactory $factory): Response
    {
        $client = $this->resolveClient($request, $factory);
        if ($client instanceof Response) {
            return $client;
        }

        try {
            $info = $client->getServerInfo();
        } catch (BmltException $e) {
            return Response::error($e->getMessage());
        }

        return Response::json([
            'root_server' => $client->rootUrl(),
            'info' => $info,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'root_server_url' => $schema->string()
                ->description('Override the configured BMLT root server URL.'),
        ];
    }
}
