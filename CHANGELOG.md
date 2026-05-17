# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `search_meetings`: new `radius_km` parameter. When set, takes precedence over
  `radius_miles` and maps to BMLT's `geo_width_km`.
- Documentation now links to the upstream
  [BMLT Semantic OpenAPI document](https://aggregator.bmltenabled.org/main_server/api/v1/openapi-semantic.json)
  from both the README architecture section and the `/reference` page.

## [0.2.0] - 2026-05-17

### Added
- Public landing page (`/`) rewritten to position the service as the authoritative
  AI-accessible source for Narcotics Anonymous meeting data. Highlights that BMLT
  hosts ~85% of NA meetings worldwide and that the default backend is the BMLT
  aggregator federating every public root server.
- New `/reference` page documenting every MCP tool, parameter types and defaults,
  and the corresponding BMLT Semantic Search API mapping.
- SEO and AI-discoverability:
  - `<title>`, meta description, canonical, OpenGraph, and Twitter card tags.
  - JSON-LD structured data: `WebSite` with `SearchAction`, `SoftwareApplication`,
    and `FAQPage`.
  - Visible FAQ section matching the schema markup.
  - `public/robots.txt` (allow crawl, disallow `POST /mcp`) with sitemap pointer.
  - `public/sitemap.xml` listing `/` and `/reference`.
- Per-client connection snippets on the landing page for Claude Desktop
  (custom connector + config file), Claude Code, ChatGPT / OpenAI Responses API,
  and Cursor / Windsurf / Zed / Cline / Continue.
- GitHub source link in the page header.
- `.gitkeep` files in `storage/{app,framework/...,logs}` and `bootstrap/cache`
  so the directory layout survives File-Manager / zip-extraction deployments
  that drop empty dirs.

### Changed
- `storage/framework/.gitignore` no longer uses a blanket `*` rule; switched to
  the conventional Laravel allow-list (`compiled.php`, `config.php`, …) so child
  `.gitkeep` files remain trackable.
- `storage/app/.gitignore` explicitly preserves `private/` and `public/`.
- Project `.gitignore` un-ignores `storage/logs/` so the directory survives
  contributors with `logs/` in their global gitignore.

## [0.1.0] - 2026-05-11

### Added
- Initial release.
- MCP server over Streamable HTTP at `/mcp` using `laravel/mcp`.
- Six read-only tools: `search_meetings`, `get_meeting`, `list_formats`,
  `list_service_bodies`, `get_server_info`, `list_root_servers`.
- BMLT client with allowlist enforcement for the optional `root_server_url`
  tool argument (SSRF guard).
- Geocoding driver layer: Nominatim (default), Google, and Null.
- Docker compose and Dockerfile for one-command deploys.

[Unreleased]: https://github.com/bmlt-enabled/bmlt-server-mcp/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/bmlt-enabled/bmlt-server-mcp/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/bmlt-enabled/bmlt-server-mcp/releases/tag/v0.1.0
