<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\UpdateMediaMetadata;
use App\Actions\Media\UploadMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaMetadataRequest;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function options(Request $request): JsonResponse
    {
        Gate::authorize('media.read');

        $validated = $request->validate([
            'kind' => ['required', 'in:image,video'],
            'q' => ['nullable', 'string', 'max:150'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Media::query();
        $validated['kind'] === 'video'
            ? $query->where('mime', 'video/mp4')
            : $query->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp']);

        if (! empty($validated['q'])) {
            $query->where('original_name', 'like', '%'.$validated['q'].'%');
        }

        return response()->json($query->latest('id')->paginate(20)->through(
            fn (Media $media): array => [
                'id' => $media->id,
                'name' => $media->original_name,
                'mime' => $media->mime,
                'url' => $media->url(),
            ],
        ));
    }

    public function index(
        Request $request,
    ): Response {
        Gate::authorize('media.read');

        $search = trim(
            (string) $request->query(
                'q',
                '',
            ),
        );

        $query = Media::query();

        if ($search !== '') {
            $query->where(
                'original_name',
                'like',
                '%'.$search.'%',
            );
        }

        $media = $query
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(
                fn (Media $item): array => [
                    'id' => $item->id,
                    'name' => $item->original_name,
                    'mime' => $item->mime,
                    'size_bytes' => $item->size_bytes,
                    'url' => $item->url(),

                    'default_alt' => $item->default_alt,

                    'caption' => $item->caption,

                    'lock_version' => $item->lock_version,

                    'usage_count' => $item->usages()
                        ->count(),
                ],
            );

        return Inertia::render(
            'Admin/Media/Index',
            [
                'media' => $media,

                'filters' => [
                    'q' => $search,
                ],

                'can' => [
                    'upload' => request()
                        ->user()
                        ->can('media.upload'),

                    'update' => request()
                        ->user()
                        ->can('media.update'),

                    'delete' => request()
                        ->user()
                        ->can('media.delete'),
                ],
            ],
        );
    }

    public function store(
        MediaUploadRequest $request,
        UploadMedia $action,
    ): RedirectResponse {
        $action->handle(
            actor: $request->user(),
            file: $request->file('file'),

            defaultAlt: $request->string(
                'default_alt',
            )->toString()
            ?: null,

            caption: $request->string(
                'caption',
            )->toString()
            ?: null,
        );

        return back()->with(
            'success',
            'Berkas media berhasil diunggah.',
        );
    }

    public function update(
        MediaMetadataRequest $request,
        Media $media,
        UpdateMediaMetadata $action,
    ): RedirectResponse {
        $action->handle(
            actor: $request->user(),
            media: $media,

            defaultAlt: $request->string(
                'default_alt',
            )->toString()
            ?: null,

            caption: $request->string(
                'caption',
            )->toString()
            ?: null,

            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Keterangan berkas media berhasil diperbarui.',
        );
    }

    public function destroy(
        Media $media,
        DeleteMedia $action,
    ): RedirectResponse {
        Gate::authorize(
            'media.delete',
        );

        $action->handle(
            actor: request()->user(),
            media: $media,
        );

        return back()->with(
            'success',
            'Berkas media berhasil dihapus.',
        );
    }
}
