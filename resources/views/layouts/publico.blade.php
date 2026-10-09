<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'LigaManager') }}</title>

        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="min-vh-100 bg-light d-flex flex-column">
            <nav class="navbar navbar-dark bg-dark">
                <div class="container-fluid px-3 px-lg-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('publico.home') }}" class="navbar-brand fw-semibold mb-0">LigaManager</a>
                    <a href="{{ route('login') }}" class="small text-light text-decoration-none">Iniciar sesión</a>
                </div>
            </nav>

            @isset($header)
                <header class="bg-white shadow-sm">
                    <div class="container px-3 px-lg-4 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-grow-1">
                {{ $slot }}
            </main>

            <footer class="text-center small text-muted py-4">
                LigaManager &copy; {{ now()->year }}
            </footer>
        </div>
    </body>
</html>
