@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h2>Zarządzanie godzinami administratorów</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Przypisz godziny
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.hours.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="admin_id" class="form-label">Administrator</label>
                            <select class="form-select" id="admin_id" name="admin_id" required>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}">
                                        {{ $admin->first_name }} {{ $admin->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dni tygodnia</label>
                            @foreach($weekDays as $key => $day)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" 
                                           name="days[]" value="{{ $key }}" id="day_{{ $key }}">
                                    <label class="form-check-label" for="day_{{ $key }}">{{ $day }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Godziny</label>
                            <div class="row">
                                @for($hour = 0; $hour < 24; $hour++)
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   name="hours[]" value="{{ $hour }}" id="hour_{{ $hour }}">
                                            <label class="form-check-label" for="hour_{{ $hour }}">
                                                {{ sprintf('%02d:00', $hour) }}
                                            </label>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Zapisz</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Aktualne przypisania
                </div>
                <div class="card-body">
                    @foreach($adminHours as $adminId => $hours)
                        <div class="admin-hours mb-4">
                            <h5>{{ $admins->find($adminId)->first_name }} {{ $admins->find($adminId)->last_name }}</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Dzień</th>
                                            <th>Godziny</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($weekDays as $dayIndex => $dayName)
                                            <tr>
                                                <td>{{ $dayName }}</td>
                                                <td>
                                                    @php
                                                        $dayHours = $hours->where('day_of_week', $dayIndex)
                                                            ->pluck('hour')
                                                            ->sort()
                                                            ->map(function($hour) {
                                                                return sprintf('%02d:00', $hour);
                                                            })
                                                            ->implode(', ');
                                                    @endphp
                                                    {{ $dayHours ?: '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 