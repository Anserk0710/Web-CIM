<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\UploadMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('media.read');

        $media = Media::query()
            ->latest()
            ->paginate(20)
            ->through(
                fn (Media $item): array => [
                    'id' => $item->id,
                    'name' => $item->original_name,
                    'mime' => $item->mime,
                    'size_bytes' => $item->size_bytes,
                    'url' => $item->url(),
                ],
            );

        return Inertia::render(
            'Admin/Media/Index',
            [
                'media' => $media,

                'can' => [
                    'upload' => request()
                        ->user()
                        ->can('media.upload'),

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
            )->toString() ?: null,
            caption: $request->string(
                'caption',
            )->toString() ?: null,
        );

        return back()->with(
            'success',
            'Media berhasil diupload.',
        );
    }

    public function destroy(
        Media $media,
        DeleteMedia $action,
    ): RedirectResponse {
        Gate::authorize('media.delete');

        $action->handle(
            actor: request()->user(),
            media: $media,
        );

        return back()->with(
            'success',
            'Media berhasil dihapus.',
        );
    }
}
