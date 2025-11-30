@extends('layouts.app')

@section('styles')
    <style>
        .no_user {
            background-color: lightcoral
        }

        .my-duty {
            background-color: {{ $myDutyColour }} !important;
        }

        .my-reserve {
            background-color: {{ $myReserveColour }} !important;
        }

        .has-duty {
            background-color: {{ $hasDutyColour }} !important;
        }

        .has-no-duty {
            background-color: {{ $noDutyColour }} !important;
        }

        .explanation-button {
            display: inline-block;
            padding: 5px;
        }

        .table-container {
            max-height: 650px;
            /* Set max height to enable scrolling */
            overflow: auto;
            position: relative;
            line-height: 1em;
        }

        .table-container td {
            padding: 0.2em !important;
        }

        /* Sticky header */
        .table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            /* Ensure the header is above the content */
        }

        td,
        th {
            text-align: center;
            vertical-align: middle;
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

        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background-color: #f8f9fa;
            padding-top: 20px;
            transition: all 0.3s;
        }

        .sidebar ul {
            padding-left: 20px;
        }

        .sidebar ul li {
            padding: 10px;
            cursor: pointer;
        }

        .sidebar.hidden {
            left: -220px;
            /* Hide the sidebar */
        }

        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }

            .table th,
            .table td {
                font-size: 12px;
                padding: 8px;
            }

            .table td:first-child,
            .table th:first-child {
                /* position: relative; Make it non-sticky on small screens */
                background-color: #f8f9fa;
            }

            /* Collapse sidebar for mobile */
            .sidebar {
                width: 100%;
                height: 100%;
                left: -100%;
                transition: left 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .card {
                margin-bottom: 20px;
                /* Adjust margin for cards on mobile */
            }

            /* Modal adjustments */
            .modal-dialog {
                max-width: 100%;
                /* Make modals full width on small screens */
                margin: 10px;
            }

            .modal-body {
                padding: 15px;
            }

            .btn-close {
                padding: 0.2rem 0.5rem;
                font-size: 1.5rem;
            }
        }

        /* Smallest mobile screens */
        @media (max-width: 480px) {

            .table th,
            .table td {
                font-size: 10px;
                /* Further reduce font size */
            }

            .table-container {
                max-height: 400px;
                /* Decrease max height for smaller screens */
            }

            .card-header {
                font-size: 10px !important;
                line-height: 10px;
            }
        }
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        Ilość osób adorujących w najbliższym czasie
                        @auth
                            <span style="display:inline-block;">, znaczenie kolorów:</span>
                            <span class="explanation-button ml-5 my-duty">Twoja adoracja</span>
                            <span class="explanation-button ml-5 my-reserve">Jesteś na liście rezerwowej</span>
                            <span class="explanation-button ml-5 has-duty">Są adorujący</span>
                            <span class="explanation-button ml-5 has-no-duty">Brak adorujących</span>
                        @endauth
                    </div>
                    <div class="card-body table-wrapper">
                        <div class="table-container">
                            <table class="table" id="current_duty_table">
                                <thead>
                                    <tr>
                                        <th class="sticky-col no-wrap">Godziny</th>
                                        @foreach ($duties as $date => $duty)
                                            <th>{{ $duty['dateFormatted'] }}</br>
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
                                                <td @php
        $classes = [];

        // --- PRIORYTET 1: jeśli komórka należy do użytkownika ---
        if ($duty['timeFrames'][$hour]['inactive'] == 0 && auth()->check()) {

            $userType = $duty['timeFrames'][$hour]['userDutyType'];

            if ($userType === 'adoracja') {
                $classes[] = 'my-duty';
            } elseif ($userType === 'rezerwa') {
                $classes[] = 'my-reserve';
            } else {
                $classes[] = 'add-duty';
            }
        }

        // --- PRIORYTET 2: jeśli NIE ma my-duty ani my-reserve ---
        if (!in_array('my-duty', $classes) && !in_array('my-reserve', $classes)) {

            if ($duty['timeFrames'][$hour]['inactive'] == 0) {
                if ($duty['timeFrames'][$hour]['adoracja'] == 0) {
                    $classes[] = 'has-no-duty';
                } elseif ($duty['timeFrames'][$hour]['adoracja'] > 0) {
                    $classes[] = 'has-duty';
                } else {
                    $classes[] = 'no-duty-cell';
                }
            }
        } @endphp
                                                    class="{{ implode(' ', $classes) }}"
                                                    @if ($duty['timeFrames'][$hour]['inactive'] == 0 && auth()->check()) data-date="{{ $date }}"
        data-hour="{{ $hour }}"
        data-duty_id="{{ $duty['timeFrames'][$hour]['dutyId'] }}" @endif>
                                                    @if ($duty['timeFrames'][$hour]['inactive'] == 1)
                                                        -
                                                    @else
                                                        {{ $duty['timeFrames'][$hour]['adoracja'] }}
                                                    @endif
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
                    <h5 class="modal-title" id="editModalLabel">Podejmuję adorację/ wpisuję się na listę rezerwową</h5>
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
                                    <input type="radio" name="duty_type" value="rezerwa" class="form-radio">
                                    <span class="ml-2">Lista rezerwowa</span>
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
    @include('current_duties.remove_duty_modal')
@endsection

@section('scripts')
    @include('current_duties.remove-duty')
    <script>
        $(document).ready(function() {

            $('#toggleMenu').click(function() {
                $('#sidebar').toggleClass('hidden');
            });

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

        $('.my-duty').on('dblclick', function() {
            const duty_id = $(this).data('duty_id');
            const date = $(this).data('date');
            const hour = $(this).data('hour');

            $('#remove-duty-hour').val(hour);
            $('#remove-duty-date').val(date);
            $('#remove-duty-duty-id').val(duty_id);

            $('#removeDutyModal').modal('show');
        });

        $('.my-reserve').on('dblclick', function() {
            const date = $(this).data('date');
            const hour = $(this).data('hour');
            const duty_id = $(this).data('duty_id');

            $('#new-duty-date').val(date);
            $('#new-duty-hour').val(hour);
            $('#new-duty-duty-id').val(duty_id);

            $('#editModal').modal('show'); // Show modal
        });


        $('.add-duty').dblclick(function() {
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
