@extends('layouts.app')

@section('styles')
@endsection

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card-body">
                    <div class="card-header">
                        Moje posługi w najbliższym czasie
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
                                                    {{ $duty['hour'] }}.00 -{{ $duty['hour'] + 1}}.00
                                                </div>
                                                <button type="button" class="btn btn-sm btn-danger delete-duty-btn"
                                                    data-duty_id="{{ $duty['current_duty_id'] }}"
                                                    data-date="{{ $duty['date'] }}"
                                                    data-hour="{{ $duty['hour'] }}" title="Usuń posługę">
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

    @include('current_duties.remove_duty_modal')


@endsection

@section('scripts')
@include('current_duties.remove-duty')
    <script>
        $(document).ready(function() {

            $('.delete-duty-btn').on('click', function() {
                const duty_id = $(this).data('duty_id');
                const date = $(this).data('date');
                const hour = $(this).data('hour');

                $('#remove-duty-hour').val(hour);
                $('#remove-duty-date').val(date);
                $('#remove-duty-duty-id').val(duty_id);

                $('#removeDutyModal').modal('show');
            });

        });
    </script>
@endsection
