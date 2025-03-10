@extends('layouts.app')

@section('styles')
@endsection

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h5 class="modal-title">Zawieś posługę</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.save-suspend') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="hidden" name="id" id="suspend_id">

                            <label for="suspend_from_date" class="form-label mr-5">Od</label>
                            <input id="suspend_from_date" type="date" name="suspend_from" value="{{ $user->suspend_from }}">
                        </div>
                        <div class="mb-3">
                            <label for="suspend_to_date" class="form-label">Do</label>
                            <input id="suspend_to_date" type="date" name="suspend_to" value="{{ $user->suspend_to }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button> --}}
                        <button type="submit" class="btn btn-primary">Zapisz</button>
                    </div>
                </form>
            </div>
        </div>
    @endsection

    @section('scripts')
    @endsection
