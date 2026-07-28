@extends('layouts.public')
@section('title', 'Track Request: ' . $request->request_number)

@section('content')
<div class="alert alert-{{ $request->status_alert_type }} shadow-sm border-0 d-flex align-items-center gap-3 mb-4 mt-3" style="border-radius:14px; padding: 16px 22px;">
    <i class="bi bi-info-circle-fill fs-3"></i>
    <div>
        <div style="font-size:15px; font-weight:700;">{{ $request->public_status_message }}</div>
    </div>
</div>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <p class="text-muted mb-1 fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">Supply Request</p>
        <h1 class="page-title fw-bold" style="color: #1e293b; font-size: 2.5rem;">{{ $request->request_number }}</h1>
    </div>
    <div class="d-flex align-items-center gap-3">
        {!! $request->status_badge !!}
        
        @if($request->status === 'issued')
            <form action="{{ route('public.claim', $request->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-lg shadow-sm fw-bold rounded-pill px-4" data-confirm="Are you sure you want to claim these supplies?">
                    <i class="bi bi-box-arrow-in-down me-2"></i> Claim Supplies
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white pt-4 pb-2 border-bottom-0 rounded-top-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-info-circle text-primary me-2"></i>Request Info</h5>
            </div>
            <div class="card-body p-0 pb-3">
                @foreach([
                    ['Requester', $request->requester_name],
                    ['Department', $request->department?->name ?? 'N/A'],
                    ['Purpose', $request->purpose],
                    ['Needed By', $request->needed_by?->format('M d, Y') ?? 'ASAP'],
                    ['Submitted', $request->created_at->format('M d, Y h:i A')],
                ] as [$label, $value])
                <div class="d-flex justify-content-between px-4 py-3 align-items-center" style="border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:13px; color:#64748b; font-weight: 500;">{{ $label }}</span>
                    <span style="font-size:14px; text-align:right; font-weight: 600; color: #334155;">{!! $value !!}</span>
                </div>
                @endforeach
            </div>
        </div>

        @if($request->rejection_reason)
        <div class="card border-danger shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h6 class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Rejection Reason</h6>
                <p class="mb-0 text-dark">{{ $request->rejection_reason }}</p>
            </div>
        </div>
        @endif

        @if($request->remarks)
        <div class="card shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark"><i class="bi bi-chat-left-text text-primary me-2"></i>Remarks</h6>
                <p class="mb-0 text-muted">{{ $request->remarks }}</p>
            </div>
        </div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-header bg-white pt-4 pb-2 border-bottom-0 rounded-top-4">
                <h5 class="fw-bold mb-0" style="color:var(--primary)"><i class="bi bi-clock-history me-2"></i>Timeline</h5>
            </div>
            <div class="list-group list-group-flush pb-2">
                
                {{-- Request Submitted --}}
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0">
                    <div>
                        <div class="fw-bold" style="font-size:14px; color: #334155;">{{ $request->requester_name }}</div>
                        <div style="font-size:12px;color:#64748b; margin-top:2px">Request Submitted</div>
                    </div>
                    <div class="text-end">
                        <div class="mb-1"><span class="badge bg-success rounded-pill px-3 py-2">Submitted</span></div>
                        <div style="font-size:11px;color:#94a3b8">{{ $request->created_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>

                {{-- Approved by Supply Unit --}}
                @if($request->approved_at || $request->status === 'rejected' || $request->status === 'pending')
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0">
                    <div>
                        <div class="fw-bold" style="font-size:14px; color: #334155;">{{ $request->approver?->name ?? 'Supply Unit' }}</div>
                        <div style="font-size:12px;color:#64748b; margin-top:2px">
                            @if($request->status === 'rejected') Request Rejected
                            @elseif($request->approved_at) Request Approved
                            @else Pending Approval
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="mb-1">
                            @if($request->status === 'rejected') <span class="badge bg-danger rounded-pill px-3 py-2">Rejected</span>
                            @elseif($request->approved_at) <span class="badge bg-primary rounded-pill px-3 py-2">Approved</span>
                            @else <span class="badge bg-light text-muted border rounded-pill px-3 py-2">Pending</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:#94a3b8">{{ $request->approved_at?->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif

                {{-- Issued --}}
                @if(($request->issued_at || $request->status === 'approved' || str_starts_with($request->status, 'partial')) && $request->status !== 'rejected' && $request->status !== 'cancelled')
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0">
                    <div>
                        <div class="fw-bold" style="font-size:14px; color: #334155;">{{ $request->issuer?->name ?? 'Supply Unit' }}</div>
                        <div style="font-size:12px;color:#64748b; margin-top:2px">
                            {{ $request->issued_at ? 'Ready for Pick-up' : 'Pending Issuance' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="mb-1">
                            @if($request->issued_at) <span class="badge bg-primary rounded-pill px-3 py-2">Issued</span>
                            @else <span class="badge bg-light text-muted border rounded-pill px-3 py-2">Pending</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:#94a3b8">{{ $request->issued_at?->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif

                {{-- Claimed --}}
                @if(($request->claimed_at || $request->status === 'issued') && $request->status !== 'rejected' && $request->status !== 'cancelled')
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0">
                    <div>
                        <div class="fw-bold" style="font-size:14px; color: #334155;">{{ $request->requester_name }}</div>
                        <div style="font-size:12px;color:#64748b; margin-top:2px">
                            {{ $request->claimed_at ? 'Supplies Claimed' : 'Pending Claim' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="mb-1">
                            @if($request->claimed_at) <span class="badge bg-success rounded-pill px-3 py-2">Claimed</span>
                            @else <span class="badge bg-light text-muted border rounded-pill px-3 py-2">Pending</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:#94a3b8">{{ $request->claimed_at?->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif
                
            </div>
        </div>

    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-header bg-white pt-4 pb-3 border-bottom-0 rounded-top-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-box-seam text-primary me-2"></i>Items Requested</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted" style="font-size: 13px;">
                        <tr>
                            <th class="ps-4 py-3">Item Description</th>
                            <th class="text-center py-3">Requested</th>
                            <th class="text-center py-3">Approved</th>
                            <th class="text-center py-3">Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->items as $item)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold" style="font-size:14px; color:#334155;">{{ $item->inventoryItem?->name }}</div>
                                <div style="font-size:12px;color:#94a3b8">{{ $item->inventoryItem?->item_code }} &bull; {{ $item->inventoryItem?->category?->name }}</div>
                            </td>
                            <td class="text-center fw-bold text-dark fs-5">{{ $item->quantity_requested }}</td>
                            <td class="text-center fs-5" style="color: #64748b;">{{ $item->quantity_approved ?? '—' }}</td>
                            <td class="text-center fw-bold fs-5" style="color: var(--primary);">{{ $item->quantity_issued > 0 ? $item->quantity_issued : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
