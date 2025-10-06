@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg rounded-4">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-center">Resetuj hasło</h4>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf <div class="mb-3">
                            <label for="email" class="form-label">Adres e-mail</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                required
                                value="{{ old('email') }}"
                                placeholder="wpisz swój e-mail">

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
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
