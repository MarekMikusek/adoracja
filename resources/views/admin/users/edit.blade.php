@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="container-title">Edytuj użytkownika</h5>
            </div>
            <form id="editUserForm" method="POST" action="{{ route('admin.users.update', ['user' => $user->id]) }}">
                @method('POST')
                @csrf()
                <div class="card-body">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Id</label>
                        <input type="text" class="form-control" id="id" name="id" required
                            value="{{ $user->id }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Imię</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                            value="{{ $user->first_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Nazwisko</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                            value="{{ $user->last_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="{{ $user->email }}">
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number"
                            value="{{ $user->phone_number }}">
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Zawieszone od:</label>
                        <input type="date" class="form-control" id="suspend_from" name="suspend_from"
                            value="{{ $user->suspend_from }}">
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Zawieszone od:</label>
                        <input type="date" class="form-control" id="suspend_to" name="suspend_to"
                            value="{{ $user->suspend_to }}">
                    </div>
                    <div class="mb-3">
                        <label for="notification_preference" class="form-label">Sposób powiadomień</label>
                        <select class="form-select" id="notification_preference" name="notification_preference" required>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin"  @if ($user->is_admin) checked  @endif>
                        <label class="form-check-label" for="is_admin">Administrator</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" href="{{ route('admin.users') }}" class="btn btn-secondary">Anuluj</button>
                    <a type="button" href="{{ route('admin.users.patterns', ['user' => $user->id]) }}" class="btn btn-success" data-user_id="">Posługi</a>
                    <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                </div>
            </form>
        </div>
    </div>
@endsection
