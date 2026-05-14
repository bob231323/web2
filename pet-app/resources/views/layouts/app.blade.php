<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Littlest — Find Your Perfect Pet')</title>
    <meta name="description" content="Browse adorable pets looking for a loving home in Cairo. Adopt a cat, dog, bird, or rabbit today!">
    <link rel="icon" type="image/png" href="{{ asset('img/Dog2.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>

    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/validation.js') }}"></script>
    <script src="{{ asset('js/API.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

</body>
</html>
