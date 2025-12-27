@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
                    <div class="card-header">Świadectwa</div>

                    <div class="card-body">


    <a href="{{ route('testimonies.create') }}" class="btn btn-primary btn-sm mb-3">
        Dodaj świadectwo
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse($testimonies as $testimony)
            <div class="col-md-10 mb-4">
                <div class="card">
                    <div class="card-body">
                        <p class="card-text"><small>{{ $testimony->nickname }}</small> : {{ Str::limit($testimony->testimony, 150) }}</p>
                        <a href="{{ route('testimonies.show', $testimony) }}" class="btn btn-sm btn-outline-primary">
                            Czytaj więcej
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <p>Brak opinii do wyświetlenia.</p>
        @endforelse
    </div>

    {{ $testimonies->links() }}
</div>
    </div>
@endsection
