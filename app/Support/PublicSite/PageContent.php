<?php

namespace App\Support\PublicSite;

use App\Models\PageSection;
use App\Models\SitePage;
use Illuminate\Support\Collection;

class PageContent
{
    /**
     * @return array{
     *          'page': SitePage|null,
     *          'sections': Collection<string, PageSection>
     * }
     */
    public function forKey(string $key): array
    {
        $page = SitePage::query()
            ->with([
                'sections',
                'ogMedia',
            ])
            ->where('key', $key)
            ->first();

        $sections = $page === null
            ? collect()
            : $page->sections
                ->where('is_enabled', true)
                ->keyBy('key');

        return [
            'page' => $page,
            'sections' => $sections,
        ];
    }

    /**
     * @param  Collection<string, PageSection>  $sections
     * @return array<string, mixed>
     */
    public function data(
        Collection $sections,
        string $key,
    ): array {
        $section = $sections->get($key);

        return $section instanceof PageSection
            ? (array) $section->data
            : [];
    }

    /**
     * Conver JSON textarea values to a PHP list.
     *
     * @return array<int, mixed>
     */
    public function listValue(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode(
                trim($value),
                true,
            );

            if (! is_array($decoded)) {
                return [];
            }

            $value = $decoded;
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values($value);
    }

    /**
     * Read ordered numeric IDs from a JSON list.
     *
     * Accepts:
     * [1, 2, 3]
     * [{"id": 1}, {"product_id": 2}]
     *
     * @return array<int, int>
     */
    public function ids(mixed $value): array
    {
        return collect($this->listValue($value))
            ->map(
                fn (mixed $item): mixed => is_array($item)
                    ? ($item['id'] ?? $item['product_id'] ?? null)
                    : $item,
            )
            ->filter(
                fn (mixed $id): bool => filter_var(
                    $id,
                    FILTER_VALIDATE_INT,
                ) !== false && (int) $id > 0,
            )
            ->map(
                fn (mixed $id): int => (int) $id,
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Only permit same-site paths, anchors and HTTP(S) URLs.
     */
    public function safeUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $url = trim($value);

        if ($url === '') {
            return null;
        }

        if (
            str_starts_with($url, '#')
        ) {
            return $url;
        }

        if (
            str_starts_with($url, '/')
            && ! str_starts_with($url, '//')
            && ! str_contains($url, '\\')
        ) {
            return $url;
        }
        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL,
            ) === false
        ) {
            return null;
        }

        $scheme = strtolower(
            (string) parse_url(
                $url,
                PHP_URL_SCHEME,
            ),
        );

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }
}
