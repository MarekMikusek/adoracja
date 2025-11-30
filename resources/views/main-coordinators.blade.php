@extends('layouts.app')

@section('styles')
<style>
    .main-coordinator-phone-number {
        width: 120px;
        display: inline-block;
        vertical-align: middle;
    }
    .phone-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-bottom: 1rem;
    }
    .phone-container i {
        font-size: 1.5rem;
        color: #0d6efd; /* Bootstrap primary color */
    }
    .ajax-email-form {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .message-box {
        flex: 1 1 50%;
        min-width: 250px;
    }
    .success-message {
        display: none;
        text-align: center;
        margin-top: 15px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <h1 class="mb-5">Główni koordynatorzy adoracji:</h1>

        @foreach ($coordinators as $coordinator)
            <div class="card text-center mb-4">
                <div class="card-header fw-bold">
                    {{ $coordinator->first_name }} {{ $coordinator->last_name }}
                </div>
                <div class="card-body">

                    <div class="phone-container">
                        <i class="fa-solid fa-phone"></i>{{ $coordinator->phone_number }}
                    </div>

                    <form class="ajax-email-form" data-id="{{ $coordinator->id }}">
                        <input
                            type="text"
                            class="form-control message-box"
                            placeholder="Wpisz treść wiadomości do koordynatora">
                        <button type="submit" class="btn btn-primary">Wyślij email</button>
                    </form>

                    <div class="alert alert-success success-message" role="alert">
                        Wiadomość została wysłana!
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    $('.ajax-email-form').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const messageInput = form.find('.message-box');
        const coordinatorId = form.data('id');
        const message = messageInput.val().trim();

        if (!message) {
            alert('Wpisz treść wiadomości przed wysłaniem.');
            return;
        }

        $.ajax({
            url: "{{ route('main-coordinator-email') }}",
            method: "POST",
            data: {
                coordinator_id: coordinatorId,
                message: message,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    const alertBox = form.siblings('.success-message');
                    alertBox.stop(true, true).fadeIn(300).delay(3000).fadeOut(600);
                    messageInput.val('');
                } else {
                    alert('Nie udało się wysłać wiadomości.');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Wystąpił błąd podczas wysyłania wiadomości.');
            }
        });
    });
});
</script>
@endsection
