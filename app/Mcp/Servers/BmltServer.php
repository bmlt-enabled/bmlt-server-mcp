<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetMeetingTool;
use App\Mcp\Tools\GetServerInfoTool;
use App\Mcp\Tools\ListFormatsTool;
use App\Mcp\Tools\ListRootServersTool;
use App\Mcp\Tools\ListServiceBodiesTool;
use App\Mcp\Tools\SearchMeetingsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('BMLT Meeting Search')]
#[Version('0.1.0')]
#[Instructions(<<<'TXT'
This server exposes read-only access to the Basic Meeting List Toolbox (BMLT) — the
directory of Narcotics Anonymous meetings. Tools query a configured BMLT root
server (or any root that the deployment allowlists via root_server_url).

Suggested flow for "find me a meeting" queries:

1. If the user names a region, call list_service_bodies first to map names → IDs.
2. If the user wants a format filter (e.g. "open speaker meetings"), call
   list_formats to map names → IDs.
3. Call search_meetings with either an address (geocoded server-side) or
   latitude/longitude + radius_miles, plus any filters from steps 1-2.
4. For deeper detail on a single meeting, call get_meeting with its id_bigint.

Use list_root_servers if the user asks about a server you do not have
configured (the BMLT aggregator publishes a directory of public roots).
TXT)]
class BmltServer extends Server
{
    protected array $tools = [
        SearchMeetingsTool::class,
        GetMeetingTool::class,
        ListFormatsTool::class,
        ListServiceBodiesTool::class,
        GetServerInfoTool::class,
        ListRootServersTool::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
