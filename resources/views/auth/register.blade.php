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
                                <label for="last_name" class="form-label">Nazwisko <small class="muted">
                                        (nieobowiązkowe)</small></label>
                                <input type="text" id="last_name" name="last_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                                <div id="email_error_message" class="alert alert-danger" style="display: none;"></div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Numer telefonu</label>
                                <input type="tel" id="phone" name="phone" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="way_of_contact" class="form-label">Proszę kontaktować się ze mną przez</label>
                                <select name="ways_of_contacts_id" id="way_of_contact" class="form-control">
                                    @foreach ($waysOfContact as $wayOfContact)
                                        <option value="{{ $wayOfContact->id }}">{{ $wayOfContact->name }}</option>
                                    @endforeach
                                </select>
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

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#email').on('focusout', function() {
                console.log('out');
                var errorText = $('#email_error_message');
                errorText.hide();
                var email = $(this).val();

                if (email.length == 0) {
                    $('#email_error_message').html('Proszę podać prawidłowy adres email');
                    $('#email_error_message').show();
                } else {
                    const url = "{{ route('check-email') }}";
                    $.ajax({
                        url: url,
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            email: email
                        },
                        success: function(response) {
                            if (response != "free") {
                                $('#email_error_message').html('Ten adres jest już zajęty');
                                $('#email_error_message').show();
                            };
                        }
                    });
                }
            });
        })
    </script>
@endsection
