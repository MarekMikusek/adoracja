@extends('layouts.app')

@section('navigation')
@include('admin.navigation')
@endsection

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
                    <div class="container  mt-3 col-6">
                        <div class="card">
                            <div class="card-header">Osoby adorujące</div>
                            <div class="card-body">

                                @forelse($duties['adoracja'] as $user_id)
                                    <p>
                                        <input type="checkbox" data-user_id="{{ $users[$user_id]->id }}"
                                            class="user-checkbox">
                                        {{ $users[$user_id]->first_name }} {{ $users[$user_id]->last_name }}
                                    </p>
                                @empty
                                    Brak
                                @endforelse

                                <div class="form-group">
                                    <label for="userSelect">Dodaj użytkownika:</label>
                                    <select id="user-select-duty" class="form-control" data-duty_id="{{ $duty->duty_id }}">
                                        <option value="">-- wybierz --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->first_name }}
                                                {{ $user->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="container mt-3 col-6">
                        <div class="card">
                            <div class="card-header">Osoby w gotowości</div>
                            <div class="card-body">

                                @forelse($duties['gotowość'] as $user_id)
                                    <p>
                                        <input type="checkbox" data-user_id="{{ $users[$user_id]->id }}"
                                            class="user-checkbox">
                                        {{ $users[$user_id]->first_name }} {{ $users[$user_id]->last_name }}
                                    </p>
                                @empty
                                    Brak
                                @endforelse

                                <div class="form-group ml-3">
                                    <label for="user-select-ready">Dodaj użytkownika:</label>
                                    <select id="user-select-ready" class="form-control"
                                        data-duty_id="{{ $duty->duty_id }}">
                                        <option value="">-- wybierz --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->first_name }}
                                                {{ $user->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
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
                    }
                });
            });

            function addUserToDuty(user_id, duty_id, duty_type) {
                $.ajax({
                    url: "{{ route('admin.current-duty.store') }}",
                    method: 'POST',
                    data: {
                        current_duty_id: duty_id,
                        user_id: user_id,
                        duty_type: duty_type,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        location.reload();

                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        alert('An error occurred: ' + error);
                        console.log(xhr.responseText);
                    }
                });
            }

            $('#user-select-duty').on('change', function() {
                const duty_id = $(this).data('duty_id');
                addUserToDuty($(this).val(), duty_id, 'adoracja');
                $("#user-select-duty option:first").prop("selected", true);
            });

            $('#user-select-ready').on('change', function() {
                const duty_id = $(this).data('duty_id');
                addUserToDuty($(this).val(), duty_id, 'gotowość');
                $("#user-select-ready option:first").prop("selected", true);
            });

            $('#sendMessages').on('click', function() {

                var selectedUsers = [];

                $('.user-checkbox:checked').each(function() {
                    selectedUsers.push($(this).data('user-id'));
                });

                const message = $('#messageText').val();
                if (selectedUsers.length < 1 || message == '') {
                    $('#dutyModal').modal('hide');
                    return;
                }
                const url = "{{ route('admin.messages') }}";
                console.log(selectedUsers);
                console.log($('#messageText').val());
                return
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
                        alert('Wysłano');
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
