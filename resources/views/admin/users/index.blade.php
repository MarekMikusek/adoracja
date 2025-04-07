@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-between mb-4">
            <div class="col-9">
                <h2>Zarządzanie użytkownikami</h2>
            </div>
            <div class="col-3">
                <a href="{{ route('admin.users.create') }}">
                <button class="btn btn-secondary">
                    Dodaj użytkownika
                </button>
            </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imię</th>
                                <th>Nazwisko</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>Powiadomienia</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->first_name }}</td>
                                    <td>{{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone_number ?? '-' }}</td>
                                    <td>{{ $user->contact_preference === 'email' ? 'Email' : 'SMS' }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.edit', ['user' => $user->id])}}" role="button" class="btn btn-sm btn-primary edit-user">
                                            Edytuj
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

