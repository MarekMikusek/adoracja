import './bootstrap';

$(document).ready(function() {
    $('#editForm').submit(function(e) {
        e.preventDefault();

        const formData = {
            // ... your existing form data ...
            type: $('input[name="type"]:checked').val(),
        };

        $.ajax({
            url: '/your-endpoint',
            method: 'POST',
            data: formData,
            success: function(response) {
                // Your success handling
            },
            error: function(xhr, status, error) {
                // Your error handling
            }
        });
    });
});
