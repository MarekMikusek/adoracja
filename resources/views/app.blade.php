<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="image-container" style="width: 100%; text-align: center; margin-bottom: 20px;">
        <img src="{{ asset('pict/adoracja.webp') }}" alt="Adoracja" style="max-width: 100%; height: auto;">
    </div>
    
</body>
</html>
