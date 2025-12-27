@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Świadectwo</h3>
                    </div>
                    <div class="card-body">
                        <p class="lead">{{ $testimony->testimony }}</p>
                        <p>
                            <small>Autor: {{ $testimony->nickname }}</small>
                        </p>
                        <p>
                            <small class="text-muted">
                                Dodano: {{ $testimony->created_at->format('d.m.Y H:i') }}
                            </small>
                        </p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('testimonies.index') }}" class="btn btn-primary">Powrót do listy</a>
                </div>
            </div>
        </div>
    </div>
@endsection
