@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title" id="dutyModalLabel">Szczegóły adoracji</h5>
            </div>

            <div class="card-body">

                <p id="dutyModalDate">Data: <b>{{ $duty->date }}</b></p>
                <p id="dutyModalHour">Godzina: <b>{{ $duty->hour }}.00 - {{ $duty->hour + 1 }}.00</b></p>

                <div class="row">
                    <div class="container mt-3 col-4">
                        <div class="card">
                            <div class="card-header">Osoby adorujące</div>
                            <div class="card-body">

                                @forelse($duties['adoracja'] as $user_id)
                                    <p>
                                        <input type="checkbox" data-user_id="{{ $users[$user_id]->id }}"
                                            class="user-checkbox">
                                        <a href="{{ route('admin.users.edit', ['user' => $users[$user_id]->id]) }}">
                                            {{ $users[$user_id]->first_name }} {{ $users[$user_id]->last_name }}
                                        </a>
                                    </p>
                                @empty
                                    Brak
                                @endforelse

                                <x-user-select id="user-select-duty" :users="$users" :duty_id="$duty->duty_id" duty_type="adoracja" label="Dodaj użytkownika do adoracji:" />

                            </div>
                        </div>
                    </div>


                    <div class="container mt-3 col-4">
                        <div class="card">
                            <div class="card-header">Osoby na liście rezerwowej</div>
                            <div class="card-body">

                                @forelse($duties['rezerwa'] as $user_id)
                                    <p>
                                        <input type="checkbox" data-user_id="{{ $users[$user_id]->id }}"
                                            class="user-checkbox">
                                        <a href="{{ route('admin.users.edit', ['user' => $users[$user_id]->id]) }}">
                                            {{ $users[$user_id]->first_name }} {{ $users[$user_id]->last_name }}
                                        </a>
                                    </p>
                                @empty
                                    Brak
                                @endforelse

                               <x-user-select id="user-select-ready" :users="$users" :duty_id="$duty->duty_id" duty_type="rezerwa" label="Dodaj użytkownika do rezerwy:" />

                            </div>
                        </div>
                    </div>

                    <div class="container mt-3 col-4">
                        <div class="card">
                            <div class="card-header">Posługa tymczasowo zawieszona</div>
                            <div class="card-body">

                                @forelse($duties['zawieszona'] as $user_id)
                                    <p>
                                        <input type="checkbox" data-user_id="{{ $users[$user_id]->id }}"
                                            class="user-checkbox">
                                        <a href="{{ route('admin.users.edit', ['user' => $users[$user_id]->id]) }}">
                                            {{ $users[$user_id]->first_name }} {{ $users[$user_id]->last_name }}
                                        </a>
                                    </p>
                                @empty
                                    Brak
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container mt-3">
                    <div class="card">
                        <div class="card-header">Akcje</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <div>
                                        <h6>Wyślij wiadomość do zaznaczonych osób:</h6>
                                        <textarea id="messageText" cols="40" rows="6"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-primary" id="sendMessages">Wyślij
                                        wiadomości</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success" id="remove-duties">Usuń zaznaczone posługi</button>
                                </div>
                            </div>
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

            $('#remove-duties').on('click', function() {
                var selectedUsers = [];
                $('.user-checkbox:checked').each(function() {
                    selectedUsers.push($(this).data('user_id'));
                });

                if (selectedUsers.length < 1) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.current-duty.delete') }}",
                    method: 'POST',
                    data: {
                        users: selectedUsers,
                        current_duty_id: {{ $duty->duty_id }},
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Błąd przy usuwaniu: ' + error);
                    }
                });
            });

            // Funkcja addUserToDuty już nie jest konieczna jeśli komponent robi AJAX samodzielnie.
            // Jeśli wolisz, możesz pozostawić i wywołać ją zamiast używania AJAX w komponencie.

            $('#sendMessages').on('click', function() {

                var selectedUsers = [];

                $('.user-checkbox:checked').each(function() {
                    selectedUsers.push($(this).data('user_id'));
                });

                const message = $('#messageText').val();
                if (selectedUsers.length < 1 || message == '') {
                    $('#dutyModal').modal('hide');
                    return;
                }
                const url = "{{ route('admin.messages') }}";

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        users_ids: JSON.stringify(selectedUsers),
                        message: message
                    },
                    success: function(response) {
                        $('#messageText').val('');
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Błąd');
                    }
                });

            });
        });
    </script>
@endsection
