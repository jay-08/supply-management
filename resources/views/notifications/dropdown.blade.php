@forelse($notifications->take(10) as $notif)
<a href="{{ $notif->link ?? route('notifications.index') }}" class="d-block px-3 py-2 text-decoration-none {{ !$notif->is_read ? 'bg-primary bg-opacity-10' : '' }}" style="border-bottom:1px solid var(--border);color:inherit">
    <div class="d-flex align-items-start gap-2">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary);font-size:12px">
            <i class="bi {{ $notif->icon ?? 'bi-bell' }}"></i>
        </div>
        <div>
            <div class="fw-semibold" style="font-size:12px;color:var(--text-main)">{{ $notif->title }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ Str::limit($notif->message, 50) }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px">{{ $notif->created_at->diffForHumans() }}</div>
        </div>
    </div>
</a>
@empty
<div class="text-center p-3 text-muted" style="font-size:12px">No new notifications</div>
@endforelse
