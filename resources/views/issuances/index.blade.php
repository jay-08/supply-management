@extends('layouts.app')
@section('title', 'Issuances')
@section('breadcrumb')
    <li class="breadcrumb-item active">Issuances</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">Issuances</h1>
        <p class="page-subtitle">Track all supply issuance records.</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <input type="month" name="month" class="form-control" value="{{ request('month', now()->format('Y-m')) }}" style="width:180px">
        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel"></i></button>
    </form>
</div>
<div class="card">
    <div class="card-body p-0">
        @if($issuances->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Issuance #</th><th>Issued To</th><th>Department</th><th>Items</th><th>Total Value</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach($issuances as $iss)
                    <tr>
                        <td><a href="{{ route('issuances.show', $iss->id) }}" class="fw-bold text-decoration-none" style="color:var(--primary)">{{ $iss->issuance_number }}</a></td>
                        <td style="font-size:13px">{{ $iss->recipient?->name }}</td>
                        <td><span class="badge bg-light text-dark">{{ $iss->department?->name }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $iss->items->count() }}</span></td>
                        <td class="fw-semibold">₱{{ number_format($iss->total_value, 2) }}</td>
                        <td style="font-size:11px;color:var(--text-muted)">{{ $iss->issued_at?->format('M d, Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('issuances.show', $iss->id) }}" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('issuances.print', $iss->id) }}" target="_blank" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-printer"></i></a>
                            <a href="{{ route('issuances.pdf', $iss->id) }}" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-file-earmark-pdf text-danger"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $issuances->links() }}</div>
        @else
        <div class="empty-state"><i class="bi bi-box-arrow-right"></i><h5>No issuances found</h5></div>
        @endif
    </div>
</div>
@endsection
