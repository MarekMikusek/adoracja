@extends('layouts.app')

@section('styles')
    <style>
        .user-duty {
            color: #23aa55
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            /* Enable horizontal scroll */
            position: relative;
        }

        .table-container {
            display: block;
            white-space: nowrap;
            /* Prevent text wrapping */
            overflow-x: auto;
            max-width: 100%;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background: white;
            text-align: left;
        }

        /* Sticky Columns */
        .sticky-col {
            position: sticky;
            left: 0;
            background: white;
            z-index: 3;
        }

        /* First column */
        .first-col {
            left: 0;
            min-width: 80px;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-11">
                <div class="card mb-4">
                    <div class="card-header">
                        Adoracja w najbliższym czasie
                    </div>
                    <div class="card-body table-wrapper">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="sticky-col">Godziny</th>
                                        @foreach ($duties as $date => $duty)
                                            <th>{{ $date }}</br>
                                                {{ $duty['dayName'] }} </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dayHours as $hour)
                                        <tr>
                                            <td class="sticky-col">{{ $hour }}</td>
                                            @foreach ($duties as $date => $duty)
                                                {{-- @dd($duty) --}}
                                                <td @auth
                                                    data-date="{{ $date }}"
                                                    data-hour="{{ $hour }}"
                                                    data-duty_id="{{ $duty['timeFrames'][$hour]['dutyId'] }}"
                                                    class="editable-cell" @endauth
                                                    @if ($duty['timeFrames'][$hour]['isUserDuty']) style="background-color: rgb(146, 146, 223);" @endif>
                                                    {{ count($duty['timeFrames'][$hour]['users']) }}</td>
                                            @endforeach

                                            {{-- <form method="POST" action="{{ route('duties.destroy', $duty) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Czy na pewno chcesz zrezygnować z tego dyżuru?')">
                                                    Zrezygnuj
                                                </button>
                                            </form> --}}

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- <div class="card">
                <div class="card-header">
                    Twoje dyżury rezerwowe
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Dzień tygodnia</th>
                                    <th>Godzina</th>
                                    <th>Powtarzanie</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reservePatterns as $pattern)
                                    <tr>
                                        <td>{{ $weekDays[$pattern->day] }}</td>
                                        <td>{{ sprintf('%02d:00', $pattern->hour) }}</td>
                                        <td>{{ $repeatPatternLabels[$pattern->repeat_pattern] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Brak zdefiniowanych dyżurów rezerwowych</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> --}}
            </div>
        </div>
    </div>

    <!-- Modal add duty -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Podejmuję adorację</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" action="#">
                        @csrf
                        <div class="mb-3">
                            <label for="new-duty-hour" class="form-label">Data</label>
                            <input type="text" class="form-control" id="new-duty-date" name="date" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="new-duty-hour" class="form-label">Godzina</label>
                            <input type="text" class="form-control" id="new-duty-hour" readonly>
                        </div>
                        <input type="hidden" id="new-duty-user-id">
                        <input type="hidden" id="new-duty-duty-id">
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
            $("#scrollLeft").click(function() {
                $(".table-container").animate({
                    scrollLeft: "-=100px"
                }, "fast");
            });

            $("#scrollRight").click(function() {
                $(".table-container").animate({
                    scrollLeft: "+=100px"
                }, "fast");
            });
        });
        $('.editable-cell').dblclick(function() {
            const date = $(this).data('date');
            const hour = $(this).data('hour');
            const duty_id = $(this).data('duty_id');

            $('#new-duty-date').val(date);
            $('#new-duty-hour').val(hour);
            $('#new-duty-duty-id').val(duty_id);

            $('#editModal').modal('show'); // Show modal
        });

        $('#editForm').submit(function(e) {
            e.preventDefault();

            const duty_id = $('#new-duty-duty-id').val();
            const url = "{{ route('current-duty.store') }}";

            $('#editModal').modal('hide');


            return $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        duty_id: duty_id
                    },
                    success: function(response) {
                        alert('Dodano');
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Błąd');
                    }
                });

        });
    </script>
@endsection
