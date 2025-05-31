@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Moje konto:</div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PUT')

                            <!-- First Name -->
                            <div class="form-group">
                                <label for="first_name">Imię</label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                    id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}">
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="form-group">
                                <label for="last_name">Nazwisko</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                    id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}">
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Way of communication -->
                            <div class="form-group">
                                <label for="way_of_communication">Proszę o kontakt ze mną przez</label>
                                <select name="ways_of_contacts_id" id="ways_of_contacts_id" class="form-control">
                                    <option value="1" @if ($user['ways_of_contacts_id'] == 1) selected @endif>Telefon
                                    </option>
                                    <option value="2" @if ($user['ways_of_contacts_id'] == 2) selected @endif>Email</option>
                                    <option value="3" @if ($user['ways_of_contacts_id'] == 3) selected @endif>SMS</option>
                                </select>
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group">
                                <label for="phone">Numer telefonu</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone_number" value="{{ old('phone', $user->phone_number) }}">
                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col3">
                                <button type="submit" class="btn btn-primary mt-3">
                                    Zapisz
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button id="remove-account" class="btn btn-danger">Usuń konto</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#remove-account').on('click', function() {
                if (confirm("Czy chcesz usunąć konto?")) {
                    const url = "{{ route('remove_account') }}";
                    $.ajax({
                        url: url,
                        _token: '{{ csrf_token() }}',
                    },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Błąd');
                });
                };
            });
        });
    </script>
@endsection
