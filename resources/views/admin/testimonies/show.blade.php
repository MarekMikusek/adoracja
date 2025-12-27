@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Świadectwo</h3>
                    </div>
                    <div class="card-body">
                        <p class="lead">{{ $testimony->testimony }}</p>
                        <p>
                            <small>Autor: {{ $testimony->nickname }}</small>
                        </p>
                        <p>
                            <small class="text-muted">
                                Dodano: {{ $testimony->created_at->format('d.m.Y H:i') }}
                            </small>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="mt-3 col-3">
                        <a href="{{ route('admin.testimonies.index') }}" class="btn btn-primary">Powrót do listy</a>
                    </div>
                    @if ($testimony->is_confirmed != 1)
                        <div class="mt-3 col-3">
                            <button role="button" class="btn btn-success confirm-testimony"
                                data-testimony-id="{{ $testimony->id }}">Potwierdź</button>
                        </div>
                    @endif
                    <div class="mt-3 col-3">
                        <button role="button" data-testimony-id="{{ $testimony->id }}"
                            class="btn btn-danger remove-testimony">Usuń</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.confirm-testimony').on('click', function() {

                const button = $(this);
                const testimony_id = button.data('testimony-id');

                $.ajax({
                    url: "{{ route('admin.testimonies.confirm') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        testimony_id: testimony_id
                    },
                    success: function(response) {
                        button.closest('.col-3').remove();
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });
            });

            $('.remove-testimony').on('click', function() {

                const button = $(this);
                const testimony_id = button.data('testimony-id');
                console.log(testimony_id);
                $.ajax({
                    url: "{{ route('admin.testimonies.remove') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        testimony_id: testimony_id
                    },
                    success: function(response) {
                        window.location.href = "{{ route('admin.testimonies.index') }}";
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });


            });

        });
    </script>
@endsection
