
<script>
$(document).ready(function() {

    $('#remove-duty-form').on('submit', function(e) {
        e.preventDefault();

        const duty_id = $('#remove-duty-duty-id').val();
        const url = "{{ route('current-duty.remove') }}";

        $('#removeDutyModal').modal('hide');

        return $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                duty_id: duty_id,
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Błąd');
            }
        });
    })
});

</script>
