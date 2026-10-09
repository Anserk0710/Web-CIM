{{-- path: D:\WEB CIM\Website CIM\cim-web\resources\views\layouts\public.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="@yield('meta_description', 'PT Cahya Intan Medika')">

    <title>
        @yield('title', config('app.name'))
    </title>

    @vite('resources/js/public.ts')
</head>

<body>
    <header class="border-b border-border bg-background">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="font-bold tracking-tight text-cim-navy">
                PT Cahya Intan Medika
            </a>

            <nav aria-label="Primary navigation" class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="font-medium text-foreground hover:text-cim-blue">
                    Home
                </a>

                <a href="{{ route('admin.login') }}"
                    class="rounded-lg bg-cim-navy px-4 py-2 font-medium text-white hover:bg-cim-blue">
                    CMS
                </a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-border bg-background">
        <div class="mx-auto max-w-7xl px-4 py-8 text-sm text-muted-foreground sm:px-6 lg:px-8">
            &copy; {{ now()->year }} PT Cahya Intan Medika.
        </div>
    </footer>
</body>

</html>