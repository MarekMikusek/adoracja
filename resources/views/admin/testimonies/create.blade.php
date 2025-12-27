@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                Dodaj świadectwo
            </div>
            <div class="card-body">
                <form action="{{ route('testimonies.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Podpis</label>
                        <input type="text" name="nickname" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Treść świadectwa</label>
                        <textarea name="testimony" class="form-control @error('testimony') is-invalid @enderror" rows="5" required>{{ old('testimony') }}</textarea>
                        @error('testimony')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success">Dodaj</button>
                    <p><small>Świadectwo jest widoczne na stronie po zatwierdzeniu przez koordynatorów.</small></p>
                    <a href="{{ route('testimonies.index') }}" class="btn btn-secondary">Powrót</a>
                </form>
            </div>
        </div>
    @endsection
