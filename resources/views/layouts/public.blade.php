<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'PT Cahya Intan Medika'))</title>

    <meta
        name="description"
        content="@yield('meta_description', data_get($publicSite, 'seo_defaults.default_description', 'Informasi produk dan perusahaan PT Cahya Intan Medika.'))"
    >

    <meta name="robots" content="@yield('robots', app()->environment('production') ? 'index,follow' : 'noindex,nofollow')">

    @if (trim($__env->yieldContent('canonical')) !== '')
        <link rel="canonical" href="@yield('canonical')">
        <meta property="og:url" content="@yield('canonical')">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
        <meta property="og:url" content="{{ url()->current() }}">
    @endif

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', config('app.name', 'PT Cahya Intan Medika'))">
    <meta property="og:description" content="@yield('meta_description', data_get($publicSite, 'seo_defaults.default_description', 'Informasi produk dan perusahaan PT Cahya Intan Medika.'))">

    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @elseif($publicSite['logo'] !== null)
        <meta property="og:image" content="{{ $publicSite['logo']->url() }}">
    @endif

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    @if($publicSite['favicon'] !== null)
        <link rel="icon" href="{{ $publicSite['favicon']->url() }}">
    @endif

    <meta name="theme-color" content="#03045e">

    @vite('resources/js/public.ts')

    <script type="application/ld+json">
        @json(
            $publicSite['organization_schema'],
            JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        )
    </script>

    @hasSection('structured_data')
        @yield('structured_data')
    @endif

    @yield('head')
</head>

<body class="min-h-screen bg-background text-foreground antialiased">
    <a
        href="#main-content"
        class="sr-only z-[100] rounded-lg bg-cim-navy px-4 py-3 text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4"
    >
        Lewati ke konten utama
    </a>

    <div
        data-scroll-progress
        aria-hidden="true"
        class="fixed left-0 top-0 z-[90] h-[3px] w-full origin-left scale-x-0 bg-gradient-to-r from-cim-blue to-cim-cyan"
    ></div>

    <header class="sticky top-0 z-50 border-b border-border/80 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
            <a
                href="{{ route('home') }}"
                class="flex min-w-0 items-center gap-3 rounded-lg"
                aria-label="Beranda PT Cahya Intan Medika"
            >
                @if($publicSite['logo'] !== null)
                    <img
                        src="{{ $publicSite['logo']->url() }}"
                        alt="{{ data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika') }}"
                        class="max-h-12 max-w-40 object-contain"
                        width="160"
                        height="48"
                    >
                @else
                    <span class="max-w-64 text-base font-bold tracking-tight text-cim-navy sm:text-lg">
                        {{ data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika') }}
                    </span>
                @endif
            </a>

            <nav
                aria-label="Navigasi utama"
                class="hidden items-center gap-5 text-sm font-medium lg:flex"
            >
                <a
                    href="{{ route('home') }}"
                    @if(request()->routeIs('home')) aria-current="page" @endif
                    class="{{ request()->routeIs('home') ? 'text-cim-blue' : 'text-slate-700 hover:text-cim-blue' }}"
                >
                    Home
                </a>

                <a
                    href="{{ route('company') }}"
                    @if(request()->routeIs('company')) aria-current="page" @endif
                    class="{{ request()->routeIs('company') ? 'text-cim-blue' : 'text-slate-700 hover:text-cim-blue' }}"
                >
                    Company
                </a>

                <a
                    href="{{ route('products.index') }}"
                    @if(request()->routeIs('products.*')) aria-current="page" @endif
                    class="{{ request()->routeIs('products.*') ? 'text-cim-blue' : 'text-slate-700 hover:text-cim-blue' }}"
                >
                    Products
                </a>

                <a
                    href="{{ route('ecatalog.public.index') }}"
                    @if(request()->routeIs('ecatalog.*')) aria-current="page" @endif
                    class="{{ request()->routeIs('ecatalog.*') ? 'text-cim-blue' : 'text-slate-700 hover:text-cim-blue' }}"
                >
                    E-Catalog
                </a>

                <a
                    href="{{ route('activities.index') }}"
                    @if(request()->routeIs('activities.*')) aria-current="page" @endif
                    class="{{ request()->routeIs('activities.*') ? 'text-cim-blue' : 'text-slate-700 hover:text-cim-blue' }}"
                >
                    Activity
                </a>

                <a
                    href="{{ route('careers.index') }}"
                    @if(request()->routeIs('careers.*')) aria-current="page" @endif
                    class="{{ request()->routeIs('careers.*') ? 'text-cim-blue' : 'text-slate-700 hover:text-cim-blue' }}"
                >
                    Career
                </a>
            </nav>

            <div class="hidden lg:block">
                <a
                    href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-cim-navy px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-cim-blue"
                >
                    Contact Us
                </a>
            </div>

            <button
                id="public-menu-toggle"
                type="button"
                aria-controls="public-mobile-navigation"
                aria-expanded="false"
                aria-label="Buka menu navigasi"
                class="inline-flex size-11 items-center justify-center rounded-lg border border-border text-cim-navy hover:bg-cim-ice/40 focus-visible:outline-offset-4 lg:hidden"
            >
                <svg
                    id="public-menu-icon"
                    aria-hidden="true"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="size-6"
                >
                    <path d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <nav
            id="public-mobile-navigation"
            aria-label="Navigasi mobile"
            hidden
            class="border-t border-border bg-white px-4 py-4 lg:hidden"
        >
            <div class="mx-auto flex max-w-7xl flex-col gap-1">
                <a href="{{ route('home') }}" class="rounded-lg px-4 py-3 hover:bg-cim-ice/40">Home</a>
                <a href="{{ route('company') }}" class="rounded-lg px-4 py-3 hover:bg-cim-ice/40">Company</a>
                <a href="{{ route('products.index') }}" class="rounded-lg px-4 py-3 hover:bg-cim-ice/40">Products</a>
                <a href="{{ route('ecatalog.public.index') }}" class="rounded-lg px-4 py-3 hover:bg-cim-ice/40">E-Catalog</a>
                <a href="{{ route('activities.index') }}" class="rounded-lg px-4 py-3 hover:bg-cim-ice/40">Activity</a>
                <a href="{{ route('careers.index') }}" class="rounded-lg px-4 py-3 hover:bg-cim-ice/40">Career</a>
                <a
                    href="{{ route('contact') }}"
                    class="mt-2 rounded-lg bg-cim-navy px-4 py-3 text-center font-semibold text-white hover:bg-cim-blue"
                >
                    Contact Us
                </a>
            </div>
        </nav>
    </header>

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-border bg-slate-50">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <section>
                <h2 class="text-base font-bold text-cim-navy">
                    {{ data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika') }}
                </h2>

                @if(data_get($publicSite, 'company.about'))
                    <p class="mt-3 max-w-sm text-sm leading-6 text-slate-600">
                        {{ data_get($publicSite, 'company.about') }}
                    </p>
                @endif
            </section>

            <section>
                <h2 class="font-semibold text-cim-navy">Navigasi</h2>

                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><a class="hover:text-cim-blue" href="{{ route('company') }}">Company</a></li>
                    <li><a class="hover:text-cim-blue" href="{{ route('products.index') }}">Products</a></li>
                    <li><a class="hover:text-cim-blue" href="{{ route('ecatalog.public.index') }}">E-Catalog</a></li>
                    <li><a class="hover:text-cim-blue" href="{{ route('activities.index') }}">Activity</a></li>
                    <li><a class="hover:text-cim-blue" href="{{ route('careers.index') }}">Career</a></li>
                    <li><a class="hover:text-cim-blue" href="{{ route('contact') }}">Contact</a></li>
                    <li><a class="hover:text-cim-blue" href="{{ route('privacy') }}">Privacy Policy</a></li>
                </ul>
            </section>

            <section>
                <h2 class="font-semibold text-cim-navy">Contact</h2>

                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    @if(data_get($publicSite, 'contact.email'))
                        <li>
                            <a href="mailto:{{ data_get($publicSite, 'contact.email') }}" class="break-all hover:text-cim-blue">
                                {{ data_get($publicSite, 'contact.email') }}
                            </a>
                        </li>
                    @endif

                    @if(data_get($publicSite, 'contact.phone'))
                        <li>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', data_get($publicSite, 'contact.phone')) }}" class="hover:text-cim-blue">
                                {{ data_get($publicSite, 'contact.phone') }}
                            </a>
                        </li>
                    @endif

                    @if($publicSite['headOffice'] !== null)
                        <li class="leading-6">
                            {{ $publicSite['headOffice']->address }}
                        </li>
                    @endif
                </ul>
            </section>

            <section>
                <h2 class="font-semibold text-cim-navy">Informasi</h2>

                @if(data_get($publicSite, 'footer.tagline'))
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ data_get($publicSite, 'footer.tagline') }}
                    </p>
                @endif

                @if(data_get($publicSite, 'contact.whatsapp'))
                    <a
                        href="{{ data_get($publicSite, 'contact.whatsapp') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-3 inline-flex text-sm font-semibold text-cim-blue hover:text-cim-navy"
                    >
                        WhatsApp
                    </a>
                @endif
            </section>
        </div>

        <div class="border-t border-border">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-sm text-slate-600 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <p>
                    {{ data_get($publicSite, 'footer.copyright', '© '.now()->year.' '.data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika').'.') }}
                </p>

                <a href="{{ route('privacy') }}" class="hover:text-cim-blue">
                    Privacy Policy
                </a>
            </div>
        </div>
    </footer>
</body>
</html>