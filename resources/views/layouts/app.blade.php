<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .nav-link.active {
            color: #fff !important;
            background-color: #4b5157 !important;
            /* Bootstrap primary */
            border-radius: 0.25rem;
            font-weight: bold;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
    </style>

    @yield('styles')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</head>

<body class="font-sans antialiased">
    <div class="row"><img src="{{ asset('images/adoracja.jpg') }}" /></div>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="collapse navbar-collapse pr-5" id="navbarNavAltMarkup">
                @auth
                    <ul class="navbar-nav">
                        @if (Auth::user()->is_admin)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">Panel koordynatora</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users') }}">Użytkownicy</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('admin.admins') }}">Koordynatorzy</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('admin.intentions') }}">Intencje</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">Adoracja</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('patterns.index') }}">Moje stałe posługi</a>
                            </li>
                        @endif
                    </ul>
                @endauth
                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('intentions') }}">Intencje modlitewne</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Zaloguj się</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Zarejestruj się</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('intentions') }}">Intencje modlitewne</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile.edit-suspend') }}">Zawieś posługę</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile.edit') }}">Moje konto</a>
                        </li>
                        <li class="nav-item">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link"
                                    style="border: none; background: none; cursor: pointer;">
                                    Wyloguj się
                                </button>
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @auth

    @endauth
    </nav>
    <!-- Page Heading -->
    @isset($header)
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>
    </div>
</body>

</html>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function() {
        var currentUrl = window.location.href;

        $('.nav-link').each(function() {
            var linkUrl = this.href;

            // Match full URL exactly
            if (currentUrl === linkUrl || currentUrl.startsWith(linkUrl)) {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
            }
        });
    });
</script>

@yield('scripts')
