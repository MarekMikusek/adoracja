@extends('layouts.app')

@section('styles')
    <style>
        .remove-intention {
            background-color: #f44336;
            /* Red background */
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            /* Change cursor to pointer (hand) */
            border-radius: 5px;
        }

        .remove-intention:hover {
            background-color: #e53935;
            /* Darker red on hover */
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header h3">
                        Intencje modlitewne
                    </div>
                    <div class="card-content">
                        <table class="table table-striped">
                            @if (count($intentions) > 0)
                            <thead>
                                <th>Intencja</th>
                                <th>Potwierdź</th>
                                <th>Usuń</th>
                            </thead>
                            @endif
                            <tbody>
                                @if (count($intentions) > 0)
                                    @foreach ($intentions as $intention)
                                        <tr>
                                            <td>{{ $intention->intention }}</td>
                                            <td>
                                                @if ($intention->is_confirmed != 1)
                                                    <button class="btn btn-sm btn-success confirm-intention"
                                                        data-intention_id="{{ $intention->id }}">Potwierdź intencję</button>
                                                @endif
                                            </td>
                                            <td><span class="remove-intention" data-intention_id="{{ $intention->id }}"><i
                                                        class="fas fa-trash"></i></span></td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>Nie ma intencji</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $(".confirm-intention").on('click', function() {
                const intention = $(this).data('intention_id');
                const url = "{{ route('admin.confirm-intention') }}";

                $.ajax({
                    url: url,
                    method: "POST",
                    data: {
                        intention: intention,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(request) {
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Błąd');
                    }
                });
            });

            $('.remove-intention').on('click', function() {

                if (confirm("Czy chcesz usunąć intencję?")) {
                    const intention_id = $(this).data('intention_id');
                    const url = "{{ route('admin.intentions.remove') }}";

                    $.ajax({
                        url: url,
                        method: "POST",
                        data: {
                            intention: intention_id,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(request) {
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Błąd');
                        }
                    });
                }
            });
        });
    </script>
@endsection
