@extends('layouts.public')

@section('title', ($page?->seo_title ?: 'Activity') . ' | PT Cahya Intan Medika')

@section(
    'meta_description',
    $page?->seo_description ?: 'Aktivitas dan informasi terbaru PT Cahya Intan Medika.'
)

@section('content')
<section class="bg-gradient-to-br from-cim-ice/60 to-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight text-cim-navy">
            {{ $hero['title'] ?? $page?->title ?? 'Activity' }}
        </h1>

        @if(!empty($hero['description']))
            <p class="mt-4 max-w-3xl leading-7 text-slate-600">
                {{ $hero['description'] }}
            </p>
        @endif
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <form method="GET" action="{{ route('activities.index') }}"
        class="grid gap-4 rounded-2xl border bg-white p-5 md:grid-cols-3">
        <div>
            <label for="q" class="mb-2 block text-sm font-medium">Search</label>
            <input id="q" name="q" value="{{ $filters['q'] }}" maxlength="100"
                class="w-full rounded-lg border border-input px-3 py-2" placeholder="Cari aktivitas...">
        </div>

        <div>
            <label for="category" class="mb-2 block text-sm font-medium">Category</label>
            <select id="category" name="category" class="w-full rounded-lg border border-input px-3 py-2">
                <option value="">Semua kategori</option>

                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit"
                class="w-full rounded-lg bg-cim-blue px-5 py-2.5 font-semibold text-white hover:bg-cim-navy">
                Search Activity
            </button>
        </div>
    </form>

    @if($activities->isEmpty())
        <div class="mt-10 rounded-2xl border border-dashed p-10 text-center">
            <h2 class="font-semibold text-cim-navy">
                Belum ada aktivitas yang sesuai.
            </h2>
        </div>
    @else
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($activities as $activity)
        @php($revision = $activity->publishedRevision)

        <article class="overflow-hidden rounded-2xl border bg-white">
            @if($revision?->featureMedia)
                <img src="{{ $revision->featureMedia->url() }}" alt="{{ $revision->title }}"
                    width="{{ $revision->featureMedia->width ?: 800 }}"
                    height="{{ $revision->featureMedia->height ?: 600 }}" loading="lazy"
                    class="aspect-[4/3] w-full object-cover">
            @endif

            <div class="p-6">
                <p class="text-xs font-bold uppercase tracking-wide text-cim-blue">
                    {{ $revision?->category?->name }}
                </p>

                <h2 class="mt-3 text-xl font-bold text-cim-navy">
                    <a href="{{ route('activities.show', $activity->public_slug) }}" class="hover:text-cim-blue">
                        {{ $revision?->title }}
                    </a>
                </h2>

                @if($revision?->excerpt)
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $revision->excerpt }}
                    </p>
                @endif

                <a href="{{ route('activities.show', $activity->public_slug) }}"
                    class="mt-5 inline-flex font-semibold text-cim-blue hover:text-cim-navy">
                    Read more
                </a>
            </div>
        </article>
        @endforeach
    </div>

    <div class="mt-10">
        {{ $activities->links() }}
    </div>
    @endif
</section>
@endsection