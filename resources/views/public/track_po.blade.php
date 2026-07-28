@extends('layouts.public')
@section('title', 'Track PO: ' . $po->po_number)

@section('content')
<div class="alert alert-{{ $po->status_alert_type }} shadow-sm border-0 d-flex align-items-center gap-3 mb-4 mt-3" style="border-radius:14px; padding: 16px 22px;">
    <i class="bi bi-info-circle-fill fs-3"></i>
    <div>
        <div style="font-size:15px; font-weight:700;">{{ $po->public_status_message }}</div>
    </div>
</div>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <p class="text-muted mb-1 fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">Purchase Order</p>
        <h1 class="page-title fw-bold" style="color: #1e293b; font-size: 2.5rem;">{{ $po->po_number }}</h1>
    </div>
    <div class="d-flex align-items-center gap-3">
        {!! $po->status_badge !!}
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white pt-4 pb-2 border-bottom-0 rounded-top-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-info-circle text-primary me-2"></i>PO Details</h5>
            </div>
            <div class="card-body p-0 pb-3">
                @foreach([
                    ['Supplier', $po->supplier?->name],
                    ['PO Date', $po->po_date?->format('M d, Y')],
                    ['Delivery Date', $po->delivery_date?->format('M d, Y') ?? 'Not set'],
                    ['Payment Terms', $po->payment_terms ?? '—'],
                    ['Remarks', $po->notes ?? '—'],
                ] as [$l, $v])
                <div class="d-flex justify-content-between px-4 py-3 align-items-center" style="border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:13px; color:#64748b; font-weight: 500;">{{ $l }}</span>
                    <span style="font-size:14px; text-align:right; font-weight: 600; color: #334155;">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
        
        {{-- Workflow Progress --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold text-dark">Workflow Progress</span>
                    <span class="fw-bold text-success">{{ $po->workflow_progress }}%</span>
                </div>
                <div class="progress" style="height:12px; border-radius:10px;">
                    <div class="progress-bar bg-success" style="width:{{ $po->workflow_progress }}%"></div>
                </div>
            </div>
        </div>

        {{-- Approval History --}}
        @if($po->approvals->count())
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white pt-4 pb-2 border-bottom-0 rounded-top-4">
                <h5 class="fw-bold mb-0" style="color:var(--primary)"><i class="bi bi-clock-history me-2"></i>Approval History</h5>
            </div>
            <div class="list-group list-group-flush pb-2">
                @foreach($po->approvals as $approval)
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0">
                    <div>
                        <div class="fw-bold" style="font-size:14px; color: #334155;">{{ $approval->approver?->name }} <span class="text-muted fw-normal">({{ $approval->level_label }})</span></div>
                        @if(in_array($approval->action, ['routed', 'sent', 'received']))
                        <div style="font-size:12px;color:#64748b; margin-top:2px">{{ $approval->remarks }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="mb-1">{!! $approval->action_badge !!}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $approval->acted_at->format('M d, H:i') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-header bg-white pt-4 pb-3 border-bottom-0 rounded-top-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-text text-primary me-2"></i>PO Attachment</h5>
            </div>
            <div class="card-body text-center p-5">
                @if($po->attachment)
                    <i class="bi bi-file-earmark-check text-success mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold mb-2">Purchase Order Document</h5>
                    <p class="text-muted mb-4">View or download the attached Purchase Order document.</p>
                    <a href="{{ asset('storage/' . $po->attachment) }}" target="_blank" class="btn btn-primary px-4 rounded-pill shadow-sm">
                        <i class="bi bi-box-arrow-up-right me-2"></i> View Attachment
                    </a>
                @else
                    <i class="bi bi-file-earmark-x text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold mb-2 text-muted">No Attachment</h5>
                    <p class="text-muted mb-0">This Purchase Order does not have a document attached.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
