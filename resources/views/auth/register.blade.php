@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Podaj swoje dane</h3>
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="first_name" class="form-label">Imię</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Nazwisko <small class="muted"> (nieobowiązkowe)</small></label>
                                <input type="text" id="last_name" name="last_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Numer telefonu <small class="muted">(nieobowiązkowy, ale przydatny)</small></label>
                                <input type="tel" id="phone" name="phone" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Hasło</label>
                                <input type="password" id="password" name="password" class="form-control" required
                                    minlength="8">
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Potwierdź hasło</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Utwórz konto</button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}">Masz już konto, zaloguj się</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
