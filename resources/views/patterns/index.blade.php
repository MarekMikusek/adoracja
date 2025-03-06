@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                Twoje godziny adoracji
            </div>
            <div class="card-body">

                <ul class="list-group">
                    @if(!empty($duties))
                    @foreach ($duties as $duty)
                        <li class="list-group-item">
                            <div class="">
                                {{ $duty['day'] }}, zaczynasz o godz. {{ $duty['hour'] }}.00,
                                {{ $intervals[$duty['repeat_interval']]['name'] }}
                                <button class="btn btn-danger ml-5 remove-duty"
                                    data-id="{{ $duty['id'] }}">Rezygnuję</button>
                                    <button class="btn btn-danger ml-5 suspend_duty"
                                    data-id="{{ $duty['id'] }}" data-bs-toggle="modal"
                                    data-bs-target="#suspendDutyModal">Zawieszam</button>
                            </div>
                        </li>
                    @endforeach
                    @else
                    <li class="list-group-item">
                        <div class="">
                            Nie masz zaplanowanych żadnych dyżurów
                        </div>
                    </li>
                    @endif
                </ul>
                <div class="row justify-content-between m-4 ">
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addDutyModal">
                            Dodaj dyżur
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                Zgłosiłaś/eś gotowość do adoracji:
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @if(!empty($reserves))
                    @foreach ($reserves as $reserve)
                        <li class="list-group-item">
                            {{ $reserve['day'] }}, godz. {{ $reserve['hour'] }}.00
                            <button class="btn btn-danger ml-5 remove-reserve" data-id="{{ $reserve['id'] }}">Rezygnuję</button>
                        </li>
                    @endforeach
                    @else
                    Nie zakrelarowałeś żadnych godzin
                    @endif
                </ul>
                <div class="row justify-content-between m-4">
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addReserveModal">
                            Dodaj dyżur
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Add Duty Modal -->
    <div class="modal fade" id="addDutyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dodaj dyżur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('patterns.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="date" class="form-label">Dzień</label>
                        <select class="form-select" id="add_pattern_day" name="day" required>
                            @foreach ($weekDays as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="hour" class="form-label">Godzina</label>
                        <select class="form-select" id="add_pattern_hour" name="hour" required>
                            @foreach ($hours as $hour)
                                <option value="{{ $hour }}">{{ sprintf('%02d:00', $hour) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="repeat_pattern" class="form-label">Powtarzanie</label>
                        <select class="form-select" id="add_duty_repeat_interval" name="repeat_interval" required>
                            @foreach($intervals as $interval)
                            <option value="{{ $interval['value'] }}">{{ $interval['name'] }}</option>
                            @endforeach

                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Suspend Duty Modal -->
    <div class="modal fade" id="suspendDutyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Zawieś dyżur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('patterns.suspend') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="hidden" name="id" id="suspend_id">

                        <label for="suspend_from_date" class="form-label">Od</label>
                        <input id="suspend_from_date" type="date" name="date_from" required>
                    </div>
                    <div class="mb-3">
                        <label for="suspend_to_date" class="form-label">Do</label>
                        <input id="suspend_to_date" type="date" name="date_to">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Add reserve Modal -->
    <div class="modal fade" id="addReserveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dodaj godziny gotowości</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('reserves.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="date" class="form-label">Dzień</label>
                        <select class="form-select" id="add_pattern_day" name="day" required>
                            @foreach ($weekDays as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="hour" class="form-label">Godzina</label>
                        <select class="form-select" id="add_pattern_hour" name="hour" required>
                            @foreach ($hours as $hour)
                                <option value="{{ $hour }}">{{ sprintf('%02d:00', $hour) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="repeat_pattern" class="form-label">Powtarzanie</label>
                        <select class="form-select" id="add_duty_repeat_interval" name="repeat_interval" required>
                            @foreach($intervals as $interval)
                            <option value="{{ $interval['value'] }}">{{ $interval['name'] }}</option>
                            @endforeach

                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </div>
            </form>
            </div>
        </div>
    </div>

@endsection


@section('styles')
    <style>
        .duty-cell {
            min-width: 150px;
            height: 60px;
            vertical-align: middle;
        }

        .duty-item {
            padding: 4px 8px;
            margin: 2px 0;
            background-color: #f8f9fa;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .duty-mine {
            background-color: #e8f5e9;
        }

        .delete-duty {
            padding: 0 6px;
            line-height: 1.2;
        }
    </style>
@endsection

@section('scripts')
    <script>
        function removeResource(id, url){
            if (!confirm('Czy na pewno chcesz zrezygnować?')) {
                    return;
                }

                return $.ajax({
                    url: url, // Adjust the endpoint as needed
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}",  // Add CSRF token
                        id: id  // Pass the resource ID to the backend
                    },
                    success: function(response) {
                        alert('Resource removed successfully!');
                        // location.reload(); // Reload page or remove element from DOM
                    },
                    error: function(xhr) {
                        alert('Error removing resource: ' + xhr.responseText);
                    }
                });
        };

        $(document).ready(function() {

            $('.suspend_duty').on('click', function(){
                $('input#suspend_id').val($(this).data('id'));
            });

            $('.remove-duty').on('click', function() {
                let dutyId = $(this).data('id'); // Get the resource ID from the button
                url = "{{ route('patterns.delete') }}";

                removeResource(dutyId, url);

            });

            $('.remove-reserve').on('click', function() {

                let reserveId = $(this).data('id'); // Get the resource ID from the button
                url = "{{ route('reserves.delete') }}";

                removeResource(reserveId, url);

            });
        });







        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-duty').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Czy na pewno chcesz usunąć ten dyżur?')) {
                        const dutyId = this.dataset.dutyId;
                        fetch(`/duties/${dutyId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        }).then(response => {
                            if (response.ok) {
                                window.location.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
