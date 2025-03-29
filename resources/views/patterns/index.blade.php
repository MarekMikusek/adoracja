@extends('layouts.app')

@section('navigation')
    @include('layouts.navigation')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                Twoje stałe posługi
            </div>
            <div class="card-body">

                <ul class="list-group">
                    @if (!empty($duties['adoracja']))
                        @foreach ($duties['adoracja'] as $duty)
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{ $duty['day'] }}, zaczynasz o godz. {{ $duty['hour'] }}.00,
                                        {{ $intervals[$duty['repeat_interval']]['name'] }}
                                    </div>
                                    <div class="col-md-6">
                                        <form method="POST"
                                            action={{ route('patterns.delete', ['dutyPattern' => $duty['id']]) }}>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger ml-5 remove-duty">Rezygnuję</button>
                                        </form>
                                    </div>
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
                        <button type="button" class="add-duty btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addDutyModal" data-duty-type="adoracja">
                            Dodaj godzinę adoracji
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
                    @if (!empty($duties['gotowość']))
                        @foreach ($duties['gotowość'] as $duty)
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{ $duty['day'] }}, godz. {{ $duty['hour'] }}.00
                                    </div>
                                    <div class="col-md-6">
                                        <form method="POST"
                                            action={{ route('patterns.delete', ['dutyPattern' => $duty['id']]) }}>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger ml-5 remove-duty">Rezygnuję</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    @else
                        Nie zakrelarowałeś żadnych godzin
                    @endif
                </ul>
                <div class="row justify-content-between m-4">
                    <div class="col-auto">
                        <button type="button" class="add-duty btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addDutyModal" data-duty-type="gotowość">
                            Dodaj godzinę gotowości
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
                    <h5 class="modal-title">Dodaj posługę</h5>
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
                                @foreach ($intervals as $interval)
                                    <option value="{{ $interval['value'] }}">{{ $interval['name'] }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div id="modal-start-date" class="d-none">
                            <div class="mb-3">
                                <input type="hidden" name="id" id="suspend_id">

                                <label for="start_date" class="form-label mr-5">Data pierwszej posługi</label>
                                <input id="start_date" type="date" name="start_date" value="">
                            </div>
                            <div class="mb-3">
                                <label class="inline-flex items-center">
                                    <span class="mr-2">Rodzaj posługi: </span>
                                    <input id="duty-type-input" type="text" name="duty_type" value="" readonly>
                                </label>
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
        $(document).ready(function() {
            $('.add-duty').click(function() {
                const dutyType = $(this).data('duty-type');
                $('#duty-type-input').val(dutyType);
            });
            $('#add_duty_repeat_interval').on('change', () => {
                const interval = $('#add_duty_repeat_interval option:selected').val();
                if(interval==1){
                    $('#modal-start-date').addClass('d-none');
                } else {
                    $('#modal-start-date').removeClass('d-none');
                };
            });
        });
    </script>
@endsection
