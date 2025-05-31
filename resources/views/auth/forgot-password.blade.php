@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg rounded-4">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-center">Resetuj hasło</h4>
                    <form action="{{ route('password.email') }}" method="POST">
                        <!-- Jeśli używasz Laravel, dodaj CSRF token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">Adres e-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                placeholder="wpisz swój e-mail">
                        </div>


                        <button type="submit" class="btn btn-primary w-100">Wyślij link do resetu hasła</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none">Wróć do logowania</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
