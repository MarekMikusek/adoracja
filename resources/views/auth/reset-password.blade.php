@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg rounded-4">
                    <div class="card-body p-4">
                        <h4 class="mb-3 text-center">Ustaw nowe hasło (Krok 2/2)</h4>

                        <form action="{{ route('password.store') }}" method="POST">
                            @csrf
                            <!-- Wymagane pola ukryte: token i email -->
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label for="email" class="form-label">Adres e-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required readonly
                                    value="{{ $email }}">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nowe hasło</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required autocomplete="new-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Potwierdź hasło</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Zmień hasło</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
