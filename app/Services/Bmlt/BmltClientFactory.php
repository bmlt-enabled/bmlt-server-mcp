<?php

namespace App\Services\Bmlt;

use Illuminate\Contracts\Config\Repository as Config;

class BmltClientFactory
{
    public function __construct(private readonly Config $config) {}

    public function default(): BmltClient
    {
        return $this->forUrl((string) $this->config->get('bmlt.root_server_url'));
    }

    public function forUrl(?string $url): BmltClient
    {
        $resolved = $this->resolveUrl($url);

        return new BmltClient(
            rootUrl: $resolved,
            timeout: (int) $this->config->get('bmlt.http.timeout', 15),
            userAgent: (string) $this->config->get('bmlt.http.user_agent'),
        );
    }

    private function resolveUrl(?string $requested): string
    {
        $default = (string) $this->config->get('bmlt.root_server_url');

        if ($requested === null || $requested === '') {
            return rtrim($default, '/');
        }

        $requested = rtrim($requested, '/');

        if (! filter_var($requested, FILTER_VALIDATE_URL) || ! preg_match('#^https?://#i', $requested)) {
            throw new BmltException("Invalid root_server_url: {$requested}");
        }

        if ($this->config->get('bmlt.allow_any_root')) {
            return $requested;
        }

        $allowlist = (array) $this->config->get('bmlt.allowed_roots', []);
        $allowlist[] = $default;
        $allowlist = array_values(array_unique(array_filter(array_map(
            fn ($u) => rtrim((string) $u, '/'),
            $allowlist,
        ))));

        if (! in_array($requested, $allowlist, true)) {
            throw new BmltException(
                "root_server_url '{$requested}' is not in BMLT_ALLOWED_ROOTS. "
                .'Add it to the allowlist or set BMLT_ALLOW_ANY_ROOT=true.'
            );
        }

        return $requested;
    }
}
