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

#[Name('get_meeting')]
#[Title('Get a single meeting by ID')]
#[Description('Fetch the full record for a single meeting by its BMLT id_bigint.')]
#[IsReadOnly]
#[IsIdempotent]
class GetMeetingTool extends Tool
{
    use ResolvesBmltClient;

    public function handle(Request $request, BmltClientFactory $factory): Response
    {
        $validated = $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        $client = $this->resolveClient($request, $factory);
        if ($client instanceof Response) {
            return $client;
        }

        try {
            $results = $client->searchMeetings(['meeting_ids' => [$validated['id']]]);
        } catch (BmltException $e) {
            return Response::error($e->getMessage());
        }

        $meeting = $results[0] ?? null;

        if ($meeting === null) {
            return Response::error("No meeting found with id {$validated['id']} on " . $client->rootUrl());
        }

        return Response::json([
            'root_server' => $client->rootUrl(),
            'meeting' => $meeting,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('BMLT meeting id_bigint.')
                ->required(),

            'root_server_url' => $schema->string()
                ->description('Override the configured BMLT root server URL.'),
        ];
    }
}
