@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-between mb-4">
            <div class="col-4">
                <h2>Użytkownicy</h2>
            </div>
            <div class="col-3 form-group d-flex align-items-center">
                <input class="form-control flex-grow-1" id="user_search" type="text" placeholder="Szukaj">
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
                                <th>Edytuj</th>
                                <th>Usuń</th>
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
                                        <a href="{{ route('admin.users.edit', ['user' => $user->id]) }}" role="button"
                                            class="btn btn-sm btn-primary edit-user">
                                            Edytuj
                                        </a>
                                    </td>
                                    <td><button class="btn btn-sm btn-danger delete-user" title="Usuń użytkowika"
                                            data-user_name="{{ $user->first_name }} {{ $user->last_name }}"
                                            data-user_id="{{ $user->id }}">
                                            <i class="fas fa-times"></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove user modal -->

    <div class="modal fade" id="removeUserModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="remove-user-form" method="POST" action="{{ route('admin.users.delete') }}">
                        @method('POST')
                        @csrf()
                        <div class="mb-3">
                            <label for="remove-duty-hour" class="form-label">Czy chcesz usunąć użytkownika:</label>
                            <input type="text" class="form-control" value="" readonly id="user_name_to_delete">
                        </div>
                        <input type="hidden" id="remove-user-id" name="user" value="">
                        <button type="submit" class="btn btn-primary">Usuń</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
                    $('.delete-user').on('click', function() {

                        $('#user_name_to_delete').val($(this).data('user_name'));
                        $('#remove-user-id').val($(this).data('user_id'));
                        $('#removeUserModal').modal('show');
                    });

                    $('#user_search').on('keyup', function() {

                        const query = $(this).val();

                        const url= "{{ route('admin.users.search') }}";

                        if (query.length >= 3) {
                            return $.ajax({
                                    url: url,
                                    type: 'POST',
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        query: query
                                    },
                                    success: function(response) {
                                        location.reload();
                                    },
                                    error: function(xhr, status, error) {
                                        alert('An error occurred: ' + error);
                                    }
                                })
                            });

                        // $('#remove-user-form').on('submit', function(e) {
                        //     e.preventDefault();
                        //     const url = "{{ route('admin.users.delete') }}";
                        //     const user = $('#remove-user-id').val();

                        // return $.ajax({
                        //     url: url,
                        //     type: 'POST',
                        //     data: {
                        //         _token: "{{ csrf_token() }}",
                        //         user: user
                        //     },
                        //     success: function(response) {
                        //         location.reload();
                        //     },
                        //     error: function(xhr, status, error) {
                        //         alert('An error occurred: ' + error);
                        //     }
                        //     });
                        // })
                    });
    </script>
@endsection
