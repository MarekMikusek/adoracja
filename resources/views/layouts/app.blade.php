<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .nav-link.active {
            color: #fff !important;
            background-color: #4b5157 !important;
            /* Using a darker shade for active link for better contrast */
            border-radius: 0.25rem;
            font-weight: bold;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        /* Optional: Add some padding to the nav links for better touch targets on mobile */
        .navbar-nav .nav-item .nav-link {
            padding: 0.5rem 1rem;
        }

        /* Adjust button link styling for consistency */
        .btn-link.nav-link {
            color: rgba(0, 0, 0, .55); /* Bootstrap's default nav-link color */
            padding: 0.5rem 1rem; /* Match nav-link padding */
        }
        .btn-link.nav-link:hover {
            color: rgba(0, 0, 0, .7); /* Bootstrap's default nav-link hover color */
            text-decoration: underline;
        }
    </style>

    @yield('styles')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

</head>

<body class="font-sans antialiased">
    <div class="row">
        <img src="{{ asset('images/adoracja.jpg') }}" class="img-fluid" alt="Adoracja Image" />
    </div>

    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <ul class="navbar-nav me-auto"> {{-- Use me-auto to push right part to the end --}}
                    @auth
                        @if (Auth::user()->is_admin)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">Panel koordynatora</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users') }}">Użytkownicy</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.admins') }}">Koordynatorzy</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.intentions') }}">Intencje</a>
                            </li>
                        @else
                            <li class="nav-item" title="Ilość adorujących na każdy dzień i godzinę">
                                <a class="nav-link" href="{{ route('home') }}">Plan adoracji</a>
                            </li>
                            <li class="nav-item" title="Twoje zaklarowane posługi, można je dodać lub usunąć">
                                <a class="nav-link" href="{{ route('patterns.index') }}">Moja deklaracja posługi</a>
                            </li>
                            <li class="nav-item" title="Twoje posługi, które przypadają w najbliższym czasie">
                                <a class="nav-link" href="{{ route('current-duty.index') }}">Moje posługi</a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav"> {{-- ms-auto is no longer needed here if me-auto was used on the left --}}
                    @guest
                        <li class="nav-item" title="Intencje polecane w modliwie, można równieć dodać własną intencję.">
                            <a class="nav-link" href="{{ route('intentions') }}">Intencje modlitewne</a>
                        </li>
                        <li class="nav-item" title="Tu są informacje o Twoich posługach">
                            <a class="nav-link" href="{{ route('login') }}">Zaloguj się</a>
                        </li>
                        <li class="nav-item" title="Zajestruj się jeśli chcesz podjąć posługę i poinformowac o tym innych">
                            <a class="nav-link" href="{{ route('register') }}">Zarejestruj się</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('intentions') }}">Intencje modlitewne</a>
                        </li>
                        <li class="nav-item" title="Jeśli nie będziesz mógł służyc przez pewien czas poinformuj o tym innych">
                            <a class="nav-link" href="{{ route('profile.edit-suspend') }}">Zawieś posługę</a>
                        </li>
                        <li class="nav-item" title="Tu moższesz sprawdzić i uaktualnić swoje dane">
                            <a class="nav-link" href="{{ route('profile.edit') }}">Moje konto</a>
                        </li>
                        <li class="nav-item">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link"
                                    style="border: none; background: none; cursor: pointer; text-align: left;">
                                    Wyloguj się
                                </button>
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow py-3 mt-3">
                <div class="container">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>

</html>

<script>
    $(document).ready(function() {
        var currentUrl = window.location.href;

        // Function to set active link
        function setActiveNavLink() {
            $('.nav-link').each(function() {
                var linkUrl = this.href;

                // Match full URL or if current URL starts with link URL (for sub-routes)
                if (currentUrl === linkUrl || (currentUrl.startsWith(linkUrl) && linkUrl !== '')) {
                    // Remove active from all and then add to the current one
                    $('.nav-link').removeClass('active');
                    $(this).addClass('active');
                }
            });
        }

        // Call on initial load
        setActiveNavLink();

        // Optional: If you have dynamic content or SPA-like navigation,
        // you might need to re-run this function after a page load or URL change.
        // For standard multi-page apps, $(document).ready is sufficient.
    });
</script>

@yield('scripts')
