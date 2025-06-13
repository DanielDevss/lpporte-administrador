@props([
    'path' => null,
    'title' => null
])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-body" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>

    <article>

        <x-layouts.navigation :path="$path" />

        <main class="container my-4">
            <h1>{{ $title ?? "Dashboard" }}</h1>
            {{ $slot }}
        </main>

    </article>

</body>

</html>