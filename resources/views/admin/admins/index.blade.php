@extends('layouts.app')

@section('navigation')
    @include('layouts.navigation')
@endsection

@section('content')
<div class="container">
    <h1>Admin Management</h1>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Admin Color</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                    <tr>
                        <td>{{ $admin->first_name }}</td>
                        <td>{{ $admin->last_name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->phone_number }}</td>
                        <td>
                            <input type="color" class="form-control color-picker" data-admin-id="{{ $admin->id }}" value="{{ $admin->color ?? '#ffffff' }}">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <a href="{{ route('admin.duty_hours') }}" class="btn btn-secondary mt-3">Admin Duty</a>
</div>
@endsection

@push('scripts')
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
                alert('Admin color updated successfully!');
            },
            error: function(xhr, status, error) {
                alert('An error occurred: ' + error);
            }
        });
    });
});
</script>
@endpush