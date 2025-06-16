@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Dodaj użytkownika</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.users.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="first_name" class="form-label">Imię</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Nazwisko<small>&nbsp;(nieobowiązkowe, wpisz coś co
                                        pozwoli odróżnić od innych osób o tym samym imieniu)</small></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Numer telefonu</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number">
                            </div>

                            <div class="mb-3">
                                <label for="contact_preference" class="form-label">Powiadomienia</label>
                                <select class="form-control" id="contact_preference" name="ways_of_contacts_id" required>
                                    <option value="1">Telefon</option>
                                    <option value="2">Email</option>
                                    <option value="3">SMS</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Hasło</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Powtórz hasło</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" <label
                                    class="form-check-label" for="is_admin">Koordynator</label>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="rodo_clause" name="rodo_clause" <label
                                    class="form-check-label" for="rodo_clause">Potwierdzam, że odebrałam/em klauzulę RODO</label>
                            </div>
                            <button type="submit" class="btn btn-primary" id="create_user_submit_btn"
                                disabled>Dodaj</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#rodo_clause').on('click', function() {
                if ($(this).is(':checked')) {
                    $('#create_user_submit_btn').prop('disabled', false);
                } else {
                    $('#create_user_submit_btn').prop('disabled', true);
                };

            })
        });
    </script>
@endsection
