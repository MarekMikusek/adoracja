@extends('layouts.app')

@section('navigation')
    @include('layouts.navigation')
@endsection

@section('content')
<div class="container">
    <h1>Odpowiedzialność admnistratorów</h1>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Dzień</th>
                    <th>Godzina</th>
                    <th>Administrator</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($dutyHours as $duty)
                    <tr>
                        <td>{{ $duty->day }}</td>
                        <td>{{ $duty->hour }}</td>
                        {{-- @dd($admins) --}}
                        <td>
                            <select class="form-control change-admin">
                                @foreach($admins as $admin)
                                    <option data-duty_id="{{ $duty->id }}" value="{{ $admin->id }}" {{ $duty->admin_id == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->first_name }} {{ $admin->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal for Assigning Admin -->
    <div class="modal fade" id="assignAdminModal" tabindex="-1" aria-labelledby="assignAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignAdminModalLabel">Assign Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assignAdminForm">
                        <div class="mb-3">
                            <label for="admin_id" class="form-label">Select Admin</label>
                            <select class="form-control" id="admin_id" name="admin_id">
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" id="day" name="day">
                        <input type="hidden" id="hour" name="hour">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.change-admin').change(function() {
        var dutyId = $(this).find('option:selected').data('duty_id');
        var adminId = $(this).val();

        $.ajax({
            url: "{{ route('admin.assign_duty_hours') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                duty_id: dutyId,
                admin_id: adminId
            },
            error: function(xhr, status, error) {
                alert('An error occurred: ' + error);
            }
        });
    });
});
</script>
@endsection