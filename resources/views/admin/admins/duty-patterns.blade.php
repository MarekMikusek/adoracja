@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Zarządzanie Godzinami Odpowiedzialności Koordynatorów</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Godzina</th>
                            @foreach($days as $day)
                                <th>{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @for($h = 0; $h < 24; $h++)
                        <tr>
                            <td class="fw-bold bg-light">{{ sprintf('%02d:00', $h) }}</td>
                            @foreach($days as $day)
                                @php
                                    $currentAdminId = $patterns[$day][$h]->admin_id ?? null;
                                @endphp
                                <td>
                                    <select
                                        class="form-select form-select-sm duty-selector"
                                        data-day="{{ $day }}"
                                        data-hour="{{ $h }}"
                                        style="min-width: 150px;"
                                    >
                                        <option value="">-- brak koordynatora --</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}" {{ $currentAdminId == $admin->id ? 'selected' : '' }}>
                                                {{ $admin->first_name }} {{ $admin->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            @endforeach
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Toast do powiadomień --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="statusToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.duty-selector').forEach(select => {
    select.addEventListener('change', function() {
        const day = this.dataset.day;
        const hour = this.dataset.hour;
        const adminId = this.value;
        const selectElement = this;

        // Wizualny feedback - zablokuj select na czas zapisu
        selectElement.classList.add('is-loading');
        selectElement.disabled = true;

        fetch('{{ route("admin.duty.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                day: day,
                hour: hour,
                admin_id: adminId
            })
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, 'bg-success');
        })
        .catch(error => {
            showToast('Błąd podczas zapisu!', 'bg-danger');
            console.error('Error:', error);
        })
        .finally(() => {
            selectElement.classList.remove('is-loading');
            selectElement.disabled = false;
        });
    });
});

function showToast(message, bgColor) {
    const toastEl = document.getElementById('statusToast');
    const toastMsg = document.getElementById('toastMessage');

    toastEl.className = `toast align-items-center text-white border-0 ${bgColor}`;
    toastMsg.innerText = message;

    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}
</script>

<style>
    .table-sm select { font-size: 0.85rem; }
    .is-loading { opacity: 0.5; }
    .table-bordered td { padding: 4px; }
</style>
@endsection
