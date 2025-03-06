@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h2>Powiadomienia</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @forelse($notifications as $notification)
                <div class="notification-item p-3 mb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ $notification->data['title'] ?? 'Powiadomienie' }}</h5>
                            <p class="mb-1">{{ $notification->data['message'] }}</p>
                            <small class="text-muted">
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                        @unless($notification->read_at)
                            <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    Oznacz jako przeczytane
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <p class="mb-0">Brak powiadomień</p>
                </div>
            @endforelse

            @if($notifications->hasPages())
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.notification-item {
    background-color: #fff;
    transition: background-color 0.2s;
}

.notification-item:not(:last-child) {
    border-bottom: 1px solid #dee2e6;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e8f5e9;
}

.notification-item.unread:hover {
    background-color: #c8e6c9;
}
</style>
@endpush
@endsection 