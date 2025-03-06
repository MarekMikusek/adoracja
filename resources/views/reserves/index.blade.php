@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h2>Dyżury rezerwowe</h2>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReserveModal">
                Dodaj dyżur rezerwowy
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Dzień tygodnia</th>
                            <th>Godzina</th>
                            <th>Powtarzanie</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reserves as $reserve)
                            <tr>
                                <td>{{ $weekDays[$reserve->day_of_week] }}</td>
                                <td>{{ sprintf('%02d:00', $reserve->hour) }}</td>
                                <td>
                                    <span class="badge bg-{{ $repeatPatternColors[$reserve->repeat_pattern] }}">
                                        {{ $repeatPatternLabels[$reserve->repeat_pattern] }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-reserve" 
                                            data-reserve-id="{{ $reserve->id }}">
                                        Usuń
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Reserve Modal -->
<div class="modal fade" id="addReserveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dodaj dyżur rezerwowy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reserves.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="day_of_week" class="form-label">Dzień tygodnia</label>
                        <select class="form-select" id="day_of_week" name="day_of_week" required>
                            @foreach($weekDays as $key => $day)
                                <option value="{{ $key }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="hour" class="form-label">Godzina</label>
                        <select class="form-select" id="hour" name="hour" required>
                            @for($i = 0; $i < 24; $i++)
                                <option value="{{ $i }}">{{ sprintf('%02d:00', $i) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="repeat_pattern" class="form-label">Powtarzanie</label>
                        <select class="form-select" id="repeat_pattern" name="repeat_pattern" required>
                            @foreach($repeatPatternLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-reserve').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Czy na pewno chcesz usunąć ten dyżur rezerwowy?')) {
                const reserveId = this.dataset.reserveId;
                fetch(`/reserves/${reserveId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(response => {
                    if (response.ok) {
                        window.location.reload();
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection 