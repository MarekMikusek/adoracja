@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h2>Zarządzanie użytkownikami</h2>
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
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->first_name }}</td>
                                <td>{{ $user->last_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone_number ?? '-' }}</td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">Zweryfikowany</span>
                                    @else
                                        <span class="badge bg-warning">Niezweryfikowany</span>
                                    @endif
                                </td>
                                <td>{{ $user->notification_preference === 'email' ? 'Email' : 'SMS' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-user" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal"
                                            data-user="{{ json_encode($user) }}">
                                        Edytuj
                                    </button>
                                    @if(!$user->email_verified_at)
                                        <form method="POST" action="{{ route('admin.users.verify', $user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                Potwierdź email
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edytuj użytkownika</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Imię</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Nazwisko</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number">
                    </div>
                    <div class="mb-3">
                        <label for="notification_preference" class="form-label">Sposób powiadomień</label>
                        <select class="form-select" id="notification_preference" name="notification_preference" required>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin">
                        <label class="form-check-label" for="is_admin">Administrator</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editUserModal = document.getElementById('editUserModal');
    const editUserForm = document.getElementById('editUserForm');

    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const user = JSON.parse(this.dataset.user);
            editUserForm.action = `/admin/users/${user.id}`;
            editUserForm.querySelector('#first_name').value = user.first_name;
            editUserForm.querySelector('#last_name').value = user.last_name;
            editUserForm.querySelector('#email').value = user.email;
            editUserForm.querySelector('#phone_number').value = user.phone_number || '';
            editUserForm.querySelector('#notification_preference').value = user.notification_preference;
            editUserForm.querySelector('#is_admin').checked = user.is_admin;
        });
    });
});
</script>
@endpush
@endsection 