<?php

namespace App\Mcp\Tools\Concerns;

use App\Services\Bmlt\BmltClient;
use App\Services\Bmlt\BmltClientFactory;
use App\Services\Bmlt\BmltException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;

trait ResolvesBmltClient
{
    /**
     * Resolve a BMLT client from the request's optional `root_server_url`
     * override, or fall through to the configured default.
     */
    protected function resolveClient(Request $request, BmltClientFactory $factory): BmltClient|Response
    {
        try {
            return $factory->forUrl($request->get('root_server_url'));
        } catch (BmltException $e) {
            return Response::error($e->getMessage());
        }
    }
}
