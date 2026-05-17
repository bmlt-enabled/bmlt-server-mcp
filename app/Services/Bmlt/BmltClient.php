<?php

namespace App\Services\Bmlt;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class BmltClient
{
    public function __construct(
        private readonly string $rootUrl,
        private readonly int $timeout = 15,
        private readonly string $userAgent = 'bmlt-server-mcp/0.1',
    ) {}

    public function rootUrl(): string
    {
        return $this->rootUrl;
    }

    /**
     * GetSearchResults — meeting search.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function searchMeetings(array $params = []): array
    {
        return $this->semantic('GetSearchResults', $params);
    }

    /**
     * GetFormats — meeting format definitions.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function getFormats(array $params = []): array
    {
        return $this->semantic('GetFormats', $params);
    }

    /**
     * GetServiceBodies — service body tree.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function getServiceBodies(array $params = []): array
    {
        return $this->semantic('GetServiceBodies', $params);
    }

    /**
     * GetServerInfo — server capabilities, version, etc.
     *
     * @return array<string, mixed>
     */
    public function getServerInfo(): array
    {
        $result = $this->semantic('GetServerInfo');

        return $result[0] ?? [];
    }

    /**
     * Raw semantic call. Returns the decoded JSON body.
     *
     * @param  array<string, mixed>  $params
     * @return array<int|string, mixed>
     */
    public function semantic(string $switcher, array $params = []): array
    {
        $query = array_merge(['switcher' => $switcher, 'format' => 'json'], $this->flatten($params));

        $response = $this->http()->get($this->rootUrl.'/client_interface/json/', $query);

        if ($response->failed()) {
            throw new BmltException(sprintf(
                'BMLT request to %s (switcher=%s) failed with HTTP %d',
                $this->rootUrl,
                $switcher,
                $response->status(),
            ));
        }

        $decoded = $response->json();

        if (! is_array($decoded)) {
            throw new BmltException(sprintf(
                'BMLT request to %s (switcher=%s) returned non-JSON body',
                $this->rootUrl,
                $switcher,
            ));
        }

        return $decoded;
    }

    private function http(): PendingRequest
    {
        return Http::withHeaders([
            'User-Agent' => $this->userAgent,
            'Accept' => 'application/json',
        ])->timeout($this->timeout);
    }

    /**
     * BMLT accepts repeated `key[]=v` for arrays. Laravel's Http::get already
     * serializes arrays correctly; we just strip nulls and stringify scalars.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function flatten(array $params): array
    {
        $out = [];
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }
}
