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

    <meta name="theme-color" content="#070a0f">

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

<body class="public-site {{ request()->routeIs('home') ? 'public-home' : '' }} min-h-screen bg-[#070a0f] text-slate-100 antialiased selection:bg-[#00f0ff] selection:text-black">
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-[30vw] left-1/2 -translate-x-1/2 w-[70vw] h-[50vw] rounded-full bg-gradient-to-b from-[#00f0ff]/10 via-[#03045e]/20 to-transparent blur-3xl"></div>
    </div>

    <a href="#main-content" class="public-skip-link">Lewati ke konten utama</a>

    <div data-scroll-progress aria-hidden="true" class="public-scroll-progress"></div>

    <header class="site-header" data-public-navigation>
        <a href="{{ route('home') }}" class="site-logo" aria-label="Beranda PT Cahya Intan Medika">
            @if($publicSite['logo'] !== null)
                <img
                    src="{{ $publicSite['logo']->url() }}"
                    alt="{{ data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika') }}"
                    width="160"
                    height="48"
                >
            @else
                <span aria-hidden="true">CIM<span class="site-logo-dot">.</span></span>
            @endif
        </a>

        <button
            id="public-menu-toggle"
            class="site-menu-toggle"
            type="button"
            aria-controls="public-mobile-navigation"
            aria-expanded="false"
            aria-label="Buka menu navigasi"
        >
            <span class="site-menu-label">Menu</span>
            <span class="site-menu-icon" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
        </button>

        <nav id="public-mobile-navigation" class="site-navigation" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="site-nav-link" @if(request()->routeIs('home')) aria-current="page" @endif>
                Beranda <span class="site-nav-arrow" aria-hidden="true">↗</span>
            </a>

            <details class="site-nav-group" @if(request()->routeIs('company', 'careers.*')) data-current="true" @endif>
                <summary>Perusahaan <span class="site-nav-chevron" aria-hidden="true">+</span></summary>
                <div class="site-submenu">
                    <a href="{{ route('company') }}" @if(request()->routeIs('company')) aria-current="page" @endif>Profil Perusahaan <span aria-hidden="true">↗</span></a>
                    <a href="{{ route('careers.index') }}" @if(request()->routeIs('careers.*')) aria-current="page" @endif>Karir <span aria-hidden="true">↗</span></a>
                </div>
            </details>

            <details class="site-nav-group" @if(request()->routeIs('products.*', 'ecatalog.*')) data-current="true" @endif>
                <summary>Produk <span class="site-nav-chevron" aria-hidden="true">+</span></summary>
                <div class="site-submenu">
                    <a href="{{ route('products.index') }}" @if(request()->routeIs('products.*')) aria-current="page" @endif>Semua Produk <span aria-hidden="true">↗</span></a>
                    <a href="{{ route('ecatalog.public.index') }}" @if(request()->routeIs('ecatalog.*')) aria-current="page" @endif>E-Katalog <span aria-hidden="true">↗</span></a>
                </div>
            </details>

            <a href="{{ route('activities.index') }}" class="site-nav-link" @if(request()->routeIs('activities.*')) aria-current="page" @endif>
                Kegiatan & Berita <span class="site-nav-arrow" aria-hidden="true">↗</span>
            </a>

            <a href="{{ route('contact') }}" class="site-contact-link" @if(request()->routeIs('contact')) aria-current="page" @endif>
                Hubungi Kami <span aria-hidden="true">↗</span>
            </a>
            <p class="site-mobile-caption">{{ data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika') }}</p>
        </nav>
    </header>

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-footer-invitation">
            <p class="site-footer-eyebrow">Mulai percakapan</p>
            <a href="{{ route('contact') }}" class="site-footer-title">Mari terhubung.<span aria-hidden="true">↗</span></a>
        </div>

        <div class="site-footer-grid">
            <section class="site-footer-company">
                <h2>{{ data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika') }}</h2>
                @if(data_get($publicSite, 'company.about'))
                    <p>{{ data_get($publicSite, 'company.about') }}</p>
                @endif
                @if(data_get($publicSite, 'footer.tagline'))
                    <p>{{ data_get($publicSite, 'footer.tagline') }}</p>
                @endif
            </section>

            <section>
                <h2>Jelajahi</h2>
                <ul>
                    <li><a href="{{ route('company') }}">Profil Perusahaan</a></li>
                    <li><a href="{{ route('products.index') }}">Produk</a></li>
                    <li><a href="{{ route('ecatalog.public.index') }}">E-Katalog</a></li>
                    <li><a href="{{ route('activities.index') }}">Kegiatan & Berita</a></li>
                    <li><a href="{{ route('careers.index') }}">Karir</a></li>
                </ul>
            </section>

            <section>
                <h2>Hubungi Kami</h2>
                <ul>
                    @if(data_get($publicSite, 'contact.email'))
                        <li><a class="site-contact-value" href="mailto:{{ data_get($publicSite, 'contact.email') }}">{{ data_get($publicSite, 'contact.email') }}</a></li>
                    @endif
                    @if(data_get($publicSite, 'contact.phone'))
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', data_get($publicSite, 'contact.phone')) }}">{{ data_get($publicSite, 'contact.phone') }}</a></li>
                    @endif
                    @if(data_get($publicSite, 'contact.whatsapp'))
                        <li><a href="{{ data_get($publicSite, 'contact.whatsapp') }}" target="_blank" rel="noopener noreferrer">WhatsApp <span aria-hidden="true">↗</span></a></li>
                    @endif
                    <li><a href="{{ route('contact') }}">Formulir kontak <span aria-hidden="true">↗</span></a></li>
                </ul>
            </section>

            @if($publicSite['headOffice'] !== null)
                <section>
                    <h2>Kantor Pusat</h2>
                    <p>{{ $publicSite['headOffice']->address }}</p>
                </section>
            @endif
        </div>

        <div class="site-footer-wordmark" aria-hidden="true">CIM<span>.</span></div>

        <div class="site-footer-bottom">
            <p>{{ data_get($publicSite, 'footer.copyright', '© '.now()->year.' '.data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika').'.') }}</p>
            <a href="{{ route('privacy') }}">Kebijakan Privasi</a>
            <a href="#main-content" class="site-back-top">Kembali ke atas <span aria-hidden="true">↑</span></a>
        </div>
    </footer>
</body>
</html>
