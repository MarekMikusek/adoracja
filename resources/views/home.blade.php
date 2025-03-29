@extends('layouts.app')

@section('navigation')
    @include('layouts.navigation')
@endsection

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
            white-space: nowrap;
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
                        <div class="table-responsive table-scrollable">
                            <table class="table" id="current_duty_table">
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
                                            <td class="sticky-col">{{ $hour }}.00 - {{ $hour + 1 }}.00</td>
                                            @foreach ($duties as $date => $duty)
                                                <td
                                                    @auth
                                                    data-date="{{ $date }}"
                                                    data-hour="{{ $hour }}"
                                                    data-duty_id="{{ $duty['timeFrames'][$hour]['dutyId'] }}"
                                                    @if ($duty['timeFrames'][$hour]['userDutyType'] == 'adoracja')
                                                        style="background-color: rgb(146, 146, 223);"
                                                        class="duty-cell" title="Posłguję adoracją"
                                                    @elseif ($duty['timeFrames'][$hour]['userDutyType'] == 'gotowość')
                                                        style="background-color: rgb(16, 180, 223);"
                                                        class="duty-cell"  title="Jestem gotowy do posługi adoracji"
                                                        @else
                                                        class="no-duty-cell"
                                                    @endif @endauth>
                                                    {{ $duty['timeFrames'][$hour]['adoracja'] }}
                                                    ({{ $duty['timeFrames'][$hour]['gotowość'] }})
                                                </td>
                                            @endforeach

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal add duty -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Podejmuję adorację/ gotowość</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-duty-form">
                        <div class="mb-4">
                            <div class="flex gap-4 mb-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="duty_type" value="adoracja" class="form-radio" checked>
                                    <span class="ml-2">Adoracja</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="duty_type" value="gotowość" class="form-radio">
                                    <span class="ml-2">Gotowość</span>
                                </label>
                            </div>
                            <label for="new-duty-date" class="form-label">Data</label>
                            <input type="text" class="form-control" id="new-duty-date" name="date" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="new-duty-hour" class="form-label">Godzina</label>
                            <input type="text" class="form-control" id="new-duty-hour" readonly>
                        </div>
                        <input type="hidden" id="new-duty-duty-id">
                        <button type="submit" class="btn btn-primary">Zapisz</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove duty modal -->
    <div class="modal fade" id="removeDutyModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Rezygnuję z posługi</h5>
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

        $('.duty-cell').on('dblclick', function() {
            const duty_id = $(this).data('duty_id');
            const date = $(this).data('date');
            const hour = $(this).data('hour');

            $('#remove-duty-hour').val(hour);
            $('#remove-duty-date').val(date);
            $('#remove-duty-duty-id').val(duty_id);

            $('#removeDutyModal').modal('show');
        });

        $('#remove-duty-form').on('submit', function(e){
            e.preventDefault();

            const duty_id = $('#remove-duty-duty-id').val();
            const url = "{{ route('current-duty.remove') }}";

            $('#removeDutyModal').modal('hide');

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
    });

        $('.no-duty-cell').dblclick(function() {
            const date = $(this).data('date');
            const hour = $(this).data('hour');
            const duty_id = $(this).data('duty_id');

            $('#new-duty-date').val(date);
            $('#new-duty-hour').val(hour);
            $('#new-duty-duty-id').val(duty_id);

            $('#editModal').modal('show'); // Show modal
        });

        $('#add-duty-form').submit(function(e) {
            e.preventDefault();

            const duty_id = $('#new-duty-duty-id').val();
            const url = "{{ route('current-duty.store') }}";
            const duty_type = $('input[name="duty_type"]:checked').val();

            $('#editModal').modal('hide');

            return $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    duty_id: duty_id,
                    duty_type: duty_type
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Błąd');
                }
            });

        });
    </script>
@endsection
