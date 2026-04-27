@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-between mb-4">
            <div class="col-4">
                <h2>Użytkownicy</h2>
            </div>
            <div class="col-3">
                <a href="{{ route('admin.users.create') }}">
                    <button class="btn btn-secondary">
                        Dodaj użytkownika
                    </button>
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="users_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imię</th>
                        <th>Nazwisko</th>
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>Posługi zaplanowane</th>
                        <th>Adoracja stała</th>
                        <th>Rezerwa stała</th>
                        <th>Edytuj</th>
                        <th>Usuń</th>
                    </tr>
                    <!-- Wiersz filtrów -->
                    <tr class="filters align-middle">
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" data-col="1"
                                placeholder="Imię"></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" data-col="2"
                                placeholder="Nazwisko"></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" data-col="3"
                                placeholder="Email"></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" data-col="4"
                                placeholder="Telefon"></th>
                                <th></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" data-col="6"
                                placeholder="Szukaj dnia/godziny..."></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" data-col="7"
                                placeholder="Szukaj dnia/godziny..."></th>

                        <th colspan="2" class="text-end">
                            <button id="clearFilters" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i> Wyczyść filtry
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user['id'] }}</td>
                            <td>{{ $user['first_name'] }}</td>
                            <td>{{ $user['last_name'] }}</td>
                            <td>{{ $user['email'] }}</td>
                            <td>{{ $user['phone_number'] ?? '-' }}</td>

                            <td> <a type="button" href="{{ route('admin.users.duties', ['user' => $user['id']]) }}"
                                    class="btn btn-info btn-sm" data-user_id="">Zaplanowane</a></td>
                            <td>
                                @if ($user['adoracja'])
                                    <ul>
                                        @foreach ($user['adoracja'] as $adoracja)
                                            <li>{{ $adoracja['day'] }}, {{ $adoracja['hour'] }}-{{ $adoracja['hour'] + 1 }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td>
                                @if ($user['rezerwa'])
                                    <ul>
                                        @foreach ($user['rezerwa'] as $adoracja)
                                            <li>{{ $adoracja['day'] }},
                                                {{ $adoracja['hour'] }}-{{ $adoracja['hour'] + 1 }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td></td>
                            <td>
                                <a href="{{ route('admin.users.edit', ['user' => $user['id']]) }}" role="button"
                                    class="btn btn-sm btn-primary edit-user">
                                    Edytuj
                                </a>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-user" title="Usuń użytkownika"
                                    data-user_name="{{ $user['first_name'] }} {{ $user['last_name'] }}"
                                    data-user_id="{{ $user['id'] }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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

                // 🗑️ Obsługa modala usuwania
                $('.delete-user').on('click', function() {
                    $('#user_name_to_delete').val($(this).data('user_name'));
                    $('#remove-user-id').val($(this).data('user_id'));
                    $('#removeUserModal').modal('show');
                });

                // 🔍 Filtrowanie
                function applyFilters() {
                    const filters = {};

                    $('.filter-input').each(function() {
                        const colIndex = $(this).data('col');
                        const value = $(this).val().toLowerCase().trim();
                        if (value) {
                            filters[colIndex] = value;
                        }
                    });

                    $('#users_table tbody tr').each(function() {
                        let isVisible = true;
                        const $row = $(this);

                        // Sprawdzamy każdy aktywny filtr dla danego wiersza
                        $.each(filters, function(colIndex, filterValue) {
                            // Pobieramy komórkę o danym indeksie
                            const $cell = $row.find('td').eq(colIndex);
                            const cellText = $cell.text().toLowerCase().trim();

                            if (cellText.indexOf(filterValue) === -1) {
                                isVisible = false;
                                return false; // Przerwij pętlę $.each dla tego wiersza
                            }
                        });

                        $row.toggle(isVisible);
                    });
                }

                // 🔄 Nasłuchiwanie zmian w filtrach
                $('#users_table').on('keyup change', '.filter-input, .filter-select', applyFilters);

                // 🧹 Przycisk "Wyczyść filtry"
                $('#clearFilters').on('click', function() {
                    $('.filters input, .filters select').val('');
                    $('#users_table tbody tr').show();
                });

            });
        </script>
    @endsection
