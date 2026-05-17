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

#[Name('list_formats')]
#[Title('List meeting formats')]
#[Description(
    'List available meeting formats (Open, Closed, Speaker, Beginners, language tags, etc.) '
    .'so the caller can map human-friendly format names to BMLT format IDs.'
)]
#[IsReadOnly]
#[IsIdempotent]
class ListFormatsTool extends Tool
{
    use ResolvesBmltClient;

    public function handle(Request $request, BmltClientFactory $factory): Response
    {
        $client = $this->resolveClient($request, $factory);
        if ($client instanceof Response) {
            return $client;
        }

        $params = [];
        if ($language = $request->get('language')) {
            $params['lang_enum'] = $language;
        }
        if ($request->get('include_unused')) {
            $params['show_all'] = 1;
        }
        if ($ids = $request->get('format_ids')) {
            $params['format_ids'] = (array) $ids;
        }
        if ($keys = $request->get('key_strings')) {
            $params['key_strings'] = (array) $keys;
        }

        try {
            $formats = $client->getFormats($params);
        } catch (BmltException $e) {
            return Response::error($e->getMessage());
        }

        return Response::json([
            'root_server' => $client->rootUrl(),
            'count' => count($formats),
            'formats' => $formats,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'language' => $schema->string()
                ->description('Language code for format names (e.g. "en", "es", "fr"). Defaults to server default.'),

            'include_unused' => $schema->boolean()
                ->description('Include formats not currently assigned to any meeting. Default false.')
                ->default(false),

            'format_ids' => $schema->array()
                ->items($schema->integer())
                ->description('Restrict to specific format IDs.'),

            'key_strings' => $schema->array()
                ->items($schema->string())
                ->description('Restrict to formats matching these short codes (e.g. ["O", "C", "SP"]).'),

            'root_server_url' => $schema->string()
                ->description('Override the configured BMLT root server URL.'),
        ];
    }
}
