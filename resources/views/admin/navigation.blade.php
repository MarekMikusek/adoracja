<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="{{ route('home') }}">Plan adoracji</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup"
        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">

        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.dashboard') }}">Pland adoracji</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.users') }}">Użytkownicy</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.users') }}">Administratorzy</a>
            </li>

        </ul>

        <ul class="navbar-nav ms-auto">
            @guest
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('login') }}">Zaloguj się</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('register') }}">Utwórz konto</a>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('profile.edit-suspend') }}">Zawieś posługę</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('profile.edit') }}">Moje konto</a>
                </li>
                <li class="nav-item">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link btn btn-link"
                            style="border: none; background: none; cursor: pointer;">
                            Log Out
                        </button>
                    </form>
                </li>
            @endguest
        </ul>
    </div>
    </div>
    @auth

    @endauth
</nav>
