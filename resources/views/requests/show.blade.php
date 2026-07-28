@extends('layouts.app')
@section('title', $request->request_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('requests.index') }}" class="text-decoration-none">Requests</a></li>
    <li class="breadcrumb-item active">{{ $request->request_number }}</li>
@endsection
@section('content')
<div class="alert alert-{{ $request->status_alert_type }} shadow-sm border-0 d-flex align-items-center gap-3 mb-4" style="border-radius:12px; padding: 14px 20px;">
    <i class="bi bi-info-circle-fill fs-4"></i>
    <div>
        <div style="font-size:14px; font-weight:600;">{{ $request->status_message }}</div>
    </div>
</div>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $request->request_number }}</h1>
        <p class="page-subtitle">Submitted by {{ $request->requester_name }} · {{ $request->created_at->format('M d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        {!! $request->status_badge !!}
        @role('admin|supply-officer')
        @if($request->status === 'pending')
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal"><i class="bi bi-check-lg"></i> Approve</button>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-lg"></i> Reject</button>
        @endif
        @if($request->status === 'approved')
            <form action="{{ route('requests.issue', $request->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-box-arrow-right"></i> Issue Supplies</button>
            </form>
        @endif
        @endrole

        @if($request->status === 'issued' && auth()->id() === $request->requester_id)
            <form action="{{ route('requests.claim', $request->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" data-confirm="Are you sure you want to claim these supplies?"><i class="bi bi-box-arrow-in-down"></i> Claim Supplies</button>
            </form>
        @endif
        @if(in_array($request->status, ['pending','approved']) && (auth()->id() === $request->requester_id || auth()->user()->hasRole('admin')))
        <form action="{{ route('requests.cancel', $request->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="button" class="btn btn-outline-secondary btn-sm" data-confirm="Cancel this request?"><i class="bi bi-slash-circle"></i> Cancel</button>
        </form>
        @endif
        @role('admin')
        <form action="{{ route('requests.destroy', $request->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Are you sure you want to delete request {{ $request->request_number }}? This cannot be undone.">
                <i class="bi bi-trash"></i> Delete Request
            </button>
        </form>
        @endrole
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Request Info</h5></div>
            <div class="card-body p-0">
                @foreach([
                    ['Requester', $request->requester_name],
                    ['Department', $request->department?->name ?? 'N/A'],
                    ['Purpose', $request->purpose],
                    ['Needed By', $request->needed_by?->format('M d, Y') ?? 'ASAP'],
                    ['Status', $request->status_badge],
                    ['Submitted', $request->created_at->format('M d, Y H:i')],
                ] as [$label, $value])
                <div class="d-flex justify-content-between px-4 py-2 align-items-start" style="border-bottom:1px solid var(--border)">
                    <span style="font-size:12px;color:var(--text-muted)">{{ $label }}</span>
                    <span style="font-size:13px;text-align:right">{!! $value !!}</span>
                </div>
                @endforeach
            </div>
        </div>

        @if($request->rejection_reason)
        <div class="card border-danger">
            <div class="card-body">
                <h6 class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Rejection Reason</h6>
                <p class="mb-0" style="font-size:13px">{{ $request->rejection_reason }}</p>
            </div>
        </div>
        @endif

        @if($request->remarks)
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-chat-left-text me-1"></i>Remarks</h6>
                <p class="mb-0" style="font-size:13px">{{ $request->remarks }}</p>
            </div>
        </div>
        @endif

        <div class="card mt-4">
            <div class="card-header bg-white"><h5 class="card-title mb-0" style="color:var(--primary);font-weight:600">Request Status</h5></div>
            <div class="list-group list-group-flush">
                
                {{-- Request Submitted --}}
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                    <div>
                        <div class="fw-bold" style="font-size:13px">{{ $request->requester_name }} <span class="text-muted fw-normal">(Requester)</span></div>
                        <div style="font-size:12px;color:var(--text-muted); margin-top:2px">Request Submitted</div>
                    </div>
                    <div class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                            <span class="badge bg-success">Submitted</span>
                        </div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $request->created_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>

                {{-- Approved by Supply Unit --}}
                @if($request->approved_at || $request->status === 'rejected' || $request->status === 'pending')
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                    <div>
                        <div class="fw-bold" style="font-size:13px">{{ $request->approver?->name ?? 'Supply Unit' }} <span class="text-muted fw-normal">(Supply Unit)</span></div>
                        <div style="font-size:12px;color:var(--text-muted); margin-top:2px">
                            @if($request->status === 'rejected') Request Rejected
                            @elseif($request->approved_at) Request Approved
                            @else Pending Approval
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                            @if($request->status === 'rejected') <span class="badge bg-danger">Rejected</span>
                            @elseif($request->approved_at) <span class="badge bg-primary">Approved</span>
                            @else <span class="badge bg-light text-muted border">Pending</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $request->approved_at?->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif

                {{-- Issued --}}
                @if(($request->issued_at || $request->status === 'approved' || str_starts_with($request->status, 'partial')) && $request->status !== 'rejected' && $request->status !== 'cancelled')
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                    <div>
                        <div class="fw-bold" style="font-size:13px">{{ $request->issuer?->name ?? 'Supply Unit' }} <span class="text-muted fw-normal">(Supply Unit)</span></div>
                        <div style="font-size:12px;color:var(--text-muted); margin-top:2px">
                            {{ $request->issued_at ? 'Supplies Issued (Ready for Claim)' : 'Pending Issuance' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                            @if($request->issued_at) <span class="badge bg-primary">Issued</span>
                            @else <span class="badge bg-light text-muted border">Pending</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $request->issued_at?->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif

                {{-- Claimed --}}
                @if(($request->claimed_at || $request->status === 'issued') && $request->status !== 'rejected' && $request->status !== 'cancelled')
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                    <div>
                        <div class="fw-bold" style="font-size:13px">{{ $request->requester_name }} <span class="text-muted fw-normal">(Requester)</span></div>
                        <div style="font-size:12px;color:var(--text-muted); margin-top:2px">
                            {{ $request->claimed_at ? 'Supplies Claimed' : 'Pending Claim' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                            @if($request->claimed_at) <span class="badge bg-success">Claimed</span>
                            @else <span class="badge bg-light text-muted border">Pending</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $request->claimed_at?->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif
                
            </div>
        </div>

    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Items Requested</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Item</th><th>Requested</th><th>Approved</th><th>Issued</th><th>Notes</th></tr></thead>
                    <tbody>
                        @foreach($request->items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size:13px">{{ $item->inventoryItem?->name }}</div>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $item->inventoryItem?->item_code }} · {{ $item->inventoryItem?->unit }}</div>
                            </td>
                            <td class="fw-bold">{{ $item->quantity_requested }}</td>
                            <td>{{ $item->quantity_approved ?? '—' }}</td>
                            <td>{{ $item->quantity_issued > 0 ? $item->quantity_issued : '—' }}</td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ $item->notes ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title text-success"><i class="bi bi-check-circle-fill me-2"></i>Approve Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('requests.approve', $request->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size:13px">Set the approved quantity for each item:</p>
                @foreach($request->items as $i => $item)
                <div class="d-flex align-items-center gap-3 mb-2 p-2" style="border:1px solid var(--border);border-radius:8px">
                    <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                    <div class="flex-grow-1" style="font-size:13px;font-weight:600">{{ $item->inventoryItem?->name }}</div>
                    <div style="font-size:12px;color:var(--text-muted)">Requested: {{ $item->quantity_requested }}</div>
                    <div style="width:100px">
                        <input type="number" name="items[{{ $i }}][quantity_approved]" class="form-control form-control-sm"
                               value="{{ $item->quantity_requested }}" min="0" max="{{ $item->quantity_requested }}" required>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Approve</button></div>
        </form>
    </div></div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title text-danger"><i class="bi bi-x-circle-fill me-2"></i>Reject Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('requests.reject', $request->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <label class="form-label">Rejection Reason *</label>
                <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="State the reason for rejection..."></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
        </form>
    </div></div>
</div>
@endsection
