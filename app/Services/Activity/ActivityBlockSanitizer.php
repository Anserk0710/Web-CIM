<?php

namespace App\Services\Activity;

use App\Enums\ActivityBlockType;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ActivityBlockSanitizer
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function sanitize(
        array $blocks,
    ): array {
        if (count($blocks) > 100) {
            throw ValidationException::withMessages([
                'blocks' => 'Maximum 100 blocks.',
            ]);
        }

        $result = [];

        foreach (
            array_values($blocks) as $index => $block
        ) {
            $type = ActivityBlockType::tryFrom(
                (string) (
                    $block['type']
                    ?? ''
                ),
            );

            if ($type === null) {
                throw ValidationException::withMessages([
                    "blocks.{$index}.type" => 'Unsupported block type.',
                ]);
            }

            $result[] = [
                'block_key' => $this->blockKey(
                    $block['block_key']
                    ?? null,
                ),

                'type' => $type->value,

                'data' => $this->sanitizeData(
                    $type,
                    (array) (
                        $block['data']
                        ?? []
                    ),
                    $index,
                ),
            ];
        }

        return $result;
    }

    private function blockKey(
        mixed $key,
    ): string {
        if (
            is_string($key)
            && Str::isUuid($key)
        ) {
            return $key;
        }

        return (string) Str::uuid();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitizeData(
        ActivityBlockType $type,
        array $data,
        int $index,
    ): array {
        return match ($type) {
            ActivityBlockType::Paragraph => [
                'text' => $this->text(
                    $data['text']
                    ?? '',
                    "blocks.{$index}.data.text",
                    10000,
                ),
            ],

            ActivityBlockType::Heading => [
                'text' => $this->text(
                    $data['text']
                    ?? '',
                    "blocks.{$index}.data.text",
                    300,
                ),

                'level' => $this->headingLevel(
                    $data['level']
                    ?? 2,
                    $index,
                ),
            ],

            ActivityBlockType::Image => $this->image(
                $data,
                $index,
            ),

            ActivityBlockType::Gallery => $this->gallery(
                $data,
                $index,
            ),

            ActivityBlockType::Quote => [
                'text' => $this->text(
                    $data['text']
                    ?? '',
                    "blocks.{$index}.data.text",
                    2000,
                ),

                'attribution' => $this
                    ->optionalText(
                        $data['attribution']
                        ?? null,
                        300,
                    ),
            ],

            ActivityBlockType::Video => [
                'url' => $this->safeVideoUrl(
                    $data['url']
                    ?? '',
                    $index,
                ),

                'title' => $this
                    ->optionalText(
                        $data['title']
                        ?? null,
                        300,
                    ),
            ],

            ActivityBlockType::Cta => [
                'label' => $this->text(
                    $data['label']
                    ?? '',
                    "blocks.{$index}.data.label",
                    120,
                ),

                'url' => $this->safeCtaUrl(
                    $data['url']
                    ?? '',
                    $index,
                ),
            ],

            ActivityBlockType::RelatedProduct => [
                'product_id' => $this
                    ->productId(
                        $data['product_id']
                        ?? null,
                        $index,
                    ),
            ],

            ActivityBlockType::Divider => [],
        };
    }

    private function text(
        mixed $value,
        string $field,
        int $max,
    ): string {
        $value = trim(
            strip_tags(
                (string) $value,
            ),
        );

        if ($value === '') {
            throw ValidationException::withMessages([
                $field => 'Value is required.',
            ]);
        }

        if (mb_strlen($value) > $max) {
            throw ValidationException::withMessages([
                $field => "Maximum {$max} characters.",
            ]);
        }

        return $value;
    }

    private function optionalText(
        mixed $value,
        int $max,
    ): ?string {
        $value = trim(
            strip_tags(
                (string) ($value ?? ''),
            ),
        );

        if ($value === '') {
            return null;
        }

        return mb_substr(
            $value,
            0,
            $max,
        );
    }

    private function headingLevel(
        mixed $level,
        int $index,
    ): int {
        $level = (int) $level;

        if (
            $level < 2
            || $level > 4
        ) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.level" => 'Heading level must be 2, 3, or 4.',
            ]);
        }

        return $level;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function image(
        array $data,
        int $index,
    ): array {
        $mediaId = (int) (
            $data['media_id']
            ?? 0
        );

        $media = Media::query()
            ->find($mediaId);

        if (
            $media === null
            || ! str_starts_with(
                $media->mime,
                'image/',
            )
        ) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.media_id" => 'Valid image media is required.',
            ]);
        }

        return [
            'media_id' => $media->id,

            'alt' => $this->optionalText(
                $data['alt']
                ?? null,
                500,
            ),

            'caption' => $this->optionalText(
                $data['caption']
                ?? null,
                1000,
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function gallery(
        array $data,
        int $index,
    ): array {
        $ids = collect(
            $data['media_ids']
            ?? [],
        )
            ->map(
                fn ($id): int => (int) $id,
            )
            ->filter()
            ->unique()
            ->values();

        if (
            $ids->isEmpty()
            || $ids->count() > 50
        ) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.media_ids" => 'Gallery requires 1-50 images.',
            ]);
        }

        $count = Media::query()
            ->whereIn(
                'id',
                $ids,
            )
            ->where(
                'mime',
                'like',
                'image/%',
            )
            ->count();

        if ($count !== $ids->count()) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.media_ids" => 'Gallery contains invalid media.',
            ]);
        }

        return [
            'media_ids' => $ids->all(),

            'caption' => $this->optionalText(
                $data['caption']
                ?? null,
                1000,
            ),
        ];
    }

    private function productId(
        mixed $value,
        int $index,
    ): int {
        $productId = (int) $value;

        if (
            ! Product::query()
                ->whereKey($productId)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.product_id" => 'Related Product is invalid.',
            ]);
        }

        return $productId;
    }

    private function safeVideoUrl(
        mixed $value,
        int $index,
    ): string {
        $url = trim(
            (string) $value,
        );

        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL,
            ) === false
        ) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.url" => 'Video URL is invalid.',
            ]);
        }

        $host = strtolower(
            (string) parse_url(
                $url,
                PHP_URL_HOST,
            ),
        );

        $allowed = [
            'youtube.com',
            'www.youtube.com',
            'youtu.be',
            'vimeo.com',
            'www.vimeo.com',
        ];

        if (
            ! in_array(
                $host,
                $allowed,
                true,
            )
        ) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.url" => 'Only YouTube and Vimeo are allowed.',
            ]);
        }

        return $url;
    }

    private function safeCtaUrl(
        mixed $value,
        int $index,
    ): string {
        $url = trim(
            (string) $value,
        );

        if (
            str_starts_with(
                $url,
                '/',
            )
        ) {
            return $url;
        }

        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL,
            ) === false
        ) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.url" => 'CTA URL is invalid.',
            ]);
        }

        $scheme = strtolower(
            (string) parse_url(
                $url,
                PHP_URL_SCHEME,
            ),
        );

        if (
            ! in_array(
                $scheme,
                [
                    'http',
                    'https',
                ],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                "blocks.{$index}.data.url" => 'Only HTTP(S) CTA URLs are allowed.',
            ]);
        }

        return $url;
    }
}
