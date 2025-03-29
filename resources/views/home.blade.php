@extends('layouts.app')

@section('navigation')
@endsection

@section('styles')
    <style>
        .user-duty {
            color: #23aa55
        }

        .table-container {
            max-height: 500px;
            /* Set max height to enable scrolling */
            overflow: auto;
            position: relative;
        }

        /* Sticky header */
        .table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            /* Ensure the header is above the content */
        }

        /* Sticky first column */
        .table td:first-child,
        .table th:first-child {
            position: sticky;
            left: 0;
            background-color: #f8f9fa;

            z-index: 2;
            /* Ensure the first column is above other cells */
        }

        /* Optional: Add a border to separate the first column */
        .table td:first-child,
        .table th:first-child {
            border-right: 2px solid #ddd;
        }

        .no-wrap {
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    @include('layouts.navigation')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        Ilość osób adorujących i wyrażających gotowość do adoracji (w nawiasie)
                    </div>
                    <div class="card-body table-wrapper">
                        <div class="table-container">
                            <table class="table" id="current_duty_table">
                                <thead>
                                    <tr>
                                        <th class="sticky-col no-wrap">Godziny</th>
                                        @foreach ($duties as $date => $duty)
                                            <th>{{ $date }}</br>
                                                {{ $duty['dayName'] }} </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dayHours as $hour)
                                        <tr>
                                            <td class="sticky-col no-wrap">{{ $hour }}.00 - {{ $hour + 1 }}.00
                                            </td>
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

        $('#remove-duty-form').on('submit', function(e) {
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
