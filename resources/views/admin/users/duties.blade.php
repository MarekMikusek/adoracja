@extends('layouts.app')

@section('styles')
@endsection

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card-body">
                    <div class="card-header">
                        Posługi w najbliższym czasie - {{ $user['first_name'] }} {{ $user['last_name'] }}
                    </div>
                    <div class="card-body">

                        @if (count($duties) == 0)
                            <div class="alert alert-info" role="alert">
                                Brak przypisanych posług.
                            </div>
                        @else
                            @foreach ($duties as $dutyType => $duties)
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white"> {{-- Dostosuj kolor tła i tekstu nagłówka --}}
                                        <h2 class="h5 mb-0 text-capitalize">{{ $dutyType }}</h2> {{-- Wyświetla typ posługi (np. Adoracja, Rezerwa) z kapitalizacją pierwszej litery --}}
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        @foreach ($duties as $duty)
                                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                                duty-id="{{ $duty['current_duty_id'] }}">
                                                <div>{{ $duty['date'] }}, {{ strtolower($duty['name_of_day']) }},
                                                    <span class="ml-3 font-weight-bold">godziny </span>
                                                    {{ $duty['hour'] }}.00 -{{ $duty['hour'] + 1 }}.00
                                                </div>
                                                <button type="button" class="btn btn-sm btn-danger delete-duty-btn"
                                                    data-duty_id="{{ $duty['current_duty_id'] }}"
                                                    data-date="{{ $duty['date'] }}" data-hour="{{ $duty['hour'] }}"
                                                    title="Usuń posługę">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="removeUserDutyModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Usuń posługę</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="remove-duty-form">
                        <div class="mb-4">
                            <label for="remove-duty-date" class="form-label">Data</label>
                            <input type="text" class="form-control" id="remove-duty-date" name="date" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="remove-duty-hour" class="form-label">Godzina</label>
                            <input type="text" class="form-control" id="remove-duty-hour" readonly>
                        </div>
                        <input type="hidden" id="remove-duty-duty-id">
                        <button type="submit" class="btn btn-primary">Zapisz</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $('#remove-duty-form').on('submit', function(e) {
                e.preventDefault();

                const duty_id = $('#remove-duty-duty-id').val();
                const url = "{{ route('admin.users.remove-duty') }}";

                $('#removeUserDutyModal').modal('hide');
                
                return $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        duty_id: duty_id,
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Błąd');
                    }
                });
            })

            $('.delete-duty-btn').on('click', function() {
                const duty_id = $(this).data('duty_id');
                const date = $(this).data('date');
                const hour = $(this).data('hour');

                $('#remove-duty-hour').val(hour);
                $('#remove-duty-date').val(date);
                $('#remove-duty-duty-id').val(duty_id);

                $('#removeUserDutyModal').modal('show');
            });

        });
    </script>
@endsection
