@props([
    'id' => 'user-select-default',
    'users' => collect(),
    'duty_id' => null,
    'duty_type' => '',
    'label' => 'Dodaj użytkownika:'
])

<div class="form-group user-select-component">
    <label for="{{ $id }}">{{ $label }}</label>

    <select id="{{ $id }}"
            class="form-control user-select"
            data-duty_id="{{ $duty_id }}"
            data-duty_type="{{ $duty_type }}">
        <option value="">-- wybierz --</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
        @endforeach
    </select>
</div>

@once
    <script>
        $(document).ready(function () {
            $('.user-select').select2({
                placeholder: "-- wybierz użytkownika --",
                allowClear: true,
                width: '100%',
                language: "pl"
            });

            // obsługa wyboru użytkownika
            $('.user-select').on('select2:select', function (e) {
                const user_id = e.params.data.id;
                const duty_id = $(this).data('duty_id');
                const duty_type = $(this).data('duty_type');

                if (!user_id) return;

                $.ajax({
                    url: "{{ route('admin.current-duty.store') }}",
                    method: 'POST',
                    data: {
                        current_duty_id: duty_id,
                        user_id: user_id,
                        duty_type: duty_type,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function () {
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        alert('Błąd przy dodawaniu użytkownika: ' + error);
                        console.log(xhr.responseText);
                    }
                });

                // po dodaniu — wyczyść wybór
                $(this).val(null).trigger('change');
            });
        });
    </script>
@endonce
