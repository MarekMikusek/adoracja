@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Login</h3>
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email Field -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" required autofocus>
                            </div>

                            <!-- Password Field -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <!-- Remember Me -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>

                        <!-- Register & Forgot Password Links -->
                        <div class="text-center mt-3">
                            <a href="{{ route('register') }}">Register</a> |
                            <a href="{{ route('password.request') }}">Forgot Password?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
