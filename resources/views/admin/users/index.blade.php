@extends('layouts.app')

@section('navigation')
@include('admin.navigation')
@endsection

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
                                <th>Status</th>
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
                                    <td>
                                        {{-- @dd($user) --}}
                                        @if ($user->is_confirmed)
                                            <span class="badge bg-success">Zweryfikowany</span>
                                        @else
                                            <button type="submit" data-user_id="{{ $user->id }}"
                                                class="btn btn-success confirm-account">
                                                Potwierdź konto
                                            </button>
                                        @endif
                                    </td>
                                    <td>{{ $user->notification_preference === 'email' ? 'Email' : 'SMS' }}</td>
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


@section('scripts')
    <script>
        $(document).ready(function() {

            $('.confirm-account').on('click', function(event) {
                event.preventDefault(); // Prevent the default form submission

                $.ajax({
                    url: "{{ route('admin.user.verify') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        user_id: $(this).data("user_id")
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                        console.log(xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection
