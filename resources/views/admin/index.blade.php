@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Koordynatorzy</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Dzień</th>
                <th>Koordynator</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @for($day = 1; $day <= $daysInMonth; $day++)
            <tr>
                <td>{{ $day }}</td>
                <td>
                    <form method="POST" action="{{ route('coordinators.update') }}">
                        @csrf
                        <input type="hidden" name="day" value="{{ $day }}">
                        <select name="coordinator_responsible" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    @if(isset($patterns[$day]) && $patterns[$day] === $user->first_name . ' ' . $user->last_name) selected @endif>
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </option>
                            @endforeach
                        </select>
                </td>
                <td>
                        <button type="submit" class="btn btn-sm btn-primary">Zapisz</button>
                    </form>
                </td>
            </tr>
        @endfor
        </tbody>
    </table>
</div>
@endsection
