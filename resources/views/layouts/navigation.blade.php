<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="{{ route('home') }}">Adoracja w najbliższych dniach</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup"
        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse pr-5" id="navbarNavAltMarkup">
        @auth
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('patterns.index') }}">Moje stałe posługi</a>
                </li>

                @if (Auth::user()->is_admin)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">Widok administracyjny</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users') }}">Użytkownicy</a>
                    </li>
                @endif
            </ul>
        @endauth
        <ul class="navbar-nav ms-auto">
            @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Zaloguj się</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">Zarejestruj się</a>
                </li>
            @else
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
