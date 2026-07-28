@extends('layouts.app')
@section('title', 'Notifications')
@section('breadcrumb')
    <li class="breadcrumb-item active">Notifications</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">Notifications</h1></div>
    <form action="{{ route('notifications.read-all') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-all"></i> Mark All Read</button>
    </form>
</div>
<div class="card">
    <div class="card-body p-0">
        @forelse($notifications as $notif)
        <div class="d-flex align-items-start gap-3 px-4 py-3 {{ !$notif->is_read ? 'bg-primary bg-opacity-10' : '' }}" style="border-bottom:1px solid var(--border)">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary)">
                <i class="bi {{ $notif->icon ?? 'bi-bell' }}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:13px">{{ $notif->title }}</div>
                <div style="font-size:12px;color:var(--text-muted)">{{ $notif->message }}</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px">{{ $notif->created_at->diffForHumans() }}</div>
            </div>
            @if(!$notif->is_read)
            <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-light" style="border-radius:6px;font-size:11px"><i class="bi bi-check"></i> Read</button>
            </form>
            @endif
        </div>
        @empty
        <div class="empty-state"><i class="bi bi-bell-slash"></i><h5>No notifications</h5></div>
        @endforelse
    </div>
</div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
