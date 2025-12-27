@extends('layouts.app')

@section('styles')
<style>
    .instruction-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 80vh;
        text-align: center;
        gap: 20px; /* odstęp między elementami */
    }

    .instruction-container h1 {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .instruction-container img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease;
    }

    .instruction-container img:hover {
        transform: scale(1.03);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="instruction-container">
        <h1>Instrukcja do aplikacji:</h1>
        <a href="https://www.youtube.com/watch?v=1pBjEkAdZz8" target="_blank">
            <img src="{{ asset('images/apka_ChJZ_www.jpg') }}" alt="Film na YouTube">
        </a>
    </div>
</div>
@endsection
