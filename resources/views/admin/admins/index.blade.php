@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                Koordynatorzy
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Imię</th>
                                <th>Nazwisko</th>
                                <th>Email</th>
                                <th>Numer telefonu</th>
                                <th>Kolor koordynatora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($admins as $admin)
                                <tr>
                                    <td>{{ $admin->first_name }}</td>
                                    <td>{{ $admin->last_name }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td>{{ $admin->phone_number }}</td>
                                    <td>
                                        <input type="color" class="form-control color-picker"
                                            data-admin-id="{{ $admin->id }}" value="{{ $admin->color ?? '#ffffff' }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.duty_hours') }}" class="btn btn-secondary mt-3">Godziny odpowiedzialności</a>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.color-picker').on('change', function() {
                var adminId = $(this).data('admin-id');
                var color = $(this).val();

                $.ajax({
                    url: "{{ route('admin.update_color') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        admin_id: adminId,
                        color: color
                    },
                    success: function(response) {
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });
            });
        });
    </script>
@endsection
