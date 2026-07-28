@extends('layouts.app')
@section('title', $purchaseOrder->po_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.purchase-orders.index') }}" class="text-decoration-none">Purchase Orders</a></li>
    <li class="breadcrumb-item active">{{ $purchaseOrder->po_number }}</li>
@endsection
@section('content')
<div class="alert alert-{{ $purchaseOrder->status_alert_type }} shadow-sm border-0 d-flex align-items-center gap-3 mb-4" style="border-radius:12px; padding: 14px 20px;">
    <i class="bi bi-info-circle-fill fs-4"></i>
    <div>
        <div style="font-size:14px; font-weight:600;">{{ $purchaseOrder->status_message }}</div>
    </div>
</div>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $purchaseOrder->po_number }}</h1>
        <p class="page-subtitle">{{ $purchaseOrder->supplier?->name }} · {{ $purchaseOrder->po_date?->format('M d, Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        {!! $purchaseOrder->status_badge !!}
        <a href="{{ route('procurement.purchase-orders.print', $purchaseOrder->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> Print</a>
        <a href="{{ route('procurement.purchase-orders.pdf', $purchaseOrder->id) }}" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf"></i> PDF</a>
        
        @if($purchaseOrder->status === 'draft' && auth()->user()->hasAnyRole(['admin', 'supply-officer']))
        <form action="{{ route('procurement.purchase-orders.submit', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Submit PO</button>
        </form>
        @endif
        @if(in_array($purchaseOrder->status, ['draft', 'pending']) && auth()->user()->hasAnyRole(['admin', 'supply-officer']))
        <a href="{{ route('procurement.purchase-orders.edit', $purchaseOrder->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
        @endif

        @if($purchaseOrder->status === 'pending' && auth()->user()->hasAnyRole(['admin', 'supply-officer']))
        <form action="{{ route('procurement.purchase-orders.route-budget', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-arrow-right-circle"></i> Route to Budget</button>
        </form>
        @endif
        
        @if($purchaseOrder->status === 'returned_to_supply' && auth()->user()->hasAnyRole(['admin', 'supply-officer', 'supply-staff']))
        <form action="{{ route('procurement.purchase-orders.receive', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-box-arrow-in-down"></i> Receive PO</button>
        </form>
            @if(auth()->user()->hasAnyRole(['admin', 'supply-officer']))
            <button type="button" class="btn btn-sm btn-info text-white" onclick="openForwardModal('supply')"><i class="bi bi-arrow-right-circle"></i> Route PO</button>
            @endif
        @endif

        @if($purchaseOrder->status === 'returned_to_budget' && auth()->user()->hasAnyRole(['admin', 'budget-officer']))
            <button type="button" class="btn btn-sm btn-info text-white" onclick="openForwardModal('budget')"><i class="bi bi-arrow-right-circle"></i> Route PO</button>
        @endif

        @if($purchaseOrder->status === 'returned_to_accounting' && auth()->user()->hasAnyRole(['admin', 'accounting']))
            <button type="button" class="btn btn-sm btn-info text-white" onclick="openForwardModal('accounting')"><i class="bi bi-arrow-right-circle"></i> Route PO</button>
        @endif

        @if($purchaseOrder->status === 'routed_to_budget' && auth()->user()->hasAnyRole(['admin', 'budget-officer', 'budget-staff']))
        <form action="{{ route('procurement.purchase-orders.receive', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-box-arrow-in-down"></i> Receive PO</button>
        </form>
        @endif

        @if($purchaseOrder->status === 'received_by_budget' && auth()->user()->hasAnyRole(['admin', 'budget-officer']))
        <button type="button" class="btn btn-sm btn-primary" onclick="openApproveModal('{{ route('procurement.purchase-orders.approve-budget', $purchaseOrder->id) }}')"><i class="bi bi-check-circle"></i> Approve (Budget)</button>
        <button type="button" class="btn btn-sm btn-warning" onclick="openReturnModal('budget')"><i class="bi bi-arrow-return-left"></i> Return PO</button>
        @endif

        @if($purchaseOrder->status === 'budget_approved' && auth()->user()->hasAnyRole(['admin', 'supply-officer', 'budget-officer']))
        <form action="{{ route('procurement.purchase-orders.route-accounting', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-arrow-right-circle"></i> Route to Accounting</button>
        </form>
        @endif

        @if($purchaseOrder->status === 'routed_to_accounting' && auth()->user()->hasAnyRole(['admin', 'accounting', 'accounting-staff']))
        <form action="{{ route('procurement.purchase-orders.receive', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-box-arrow-in-down"></i> Receive PO</button>
        </form>
        @endif

        @if($purchaseOrder->status === 'received_by_accounting' && auth()->user()->hasAnyRole(['admin', 'accounting']))
        <button type="button" class="btn btn-sm btn-primary" onclick="openApproveModal('{{ route('procurement.purchase-orders.approve-accounting', $purchaseOrder->id) }}')"><i class="bi bi-check-circle"></i> Approve (Accounting)</button>
        <button type="button" class="btn btn-sm btn-warning" onclick="openReturnModal('accounting')"><i class="bi bi-arrow-return-left"></i> Return PO</button>
        @endif

        @if($purchaseOrder->status === 'accounting_approved' && auth()->user()->hasAnyRole(['admin', 'supply-officer', 'accounting']))
        <form action="{{ route('procurement.purchase-orders.route-rd', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-arrow-right-circle"></i> Route to RD/ARD</button>
        </form>
        @endif

        @if($purchaseOrder->status === 'routed_to_ard' && auth()->user()->hasAnyRole(['admin', 'assistant-regional-director', 'ard-staff']))
        <form action="{{ route('procurement.purchase-orders.receive', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-box-arrow-in-down"></i> Receive PO (ARD)</button>
        </form>
        @endif

        @if($purchaseOrder->status === 'received_by_ard' && auth()->user()->hasAnyRole(['admin', 'assistant-regional-director']))
        <button type="button" class="btn btn-sm btn-success" onclick="openApproveModal('{{ route('procurement.purchase-orders.approve-rd', $purchaseOrder->id) }}')"><i class="bi bi-check-circle"></i> Approve (ARD)</button>
        <button type="button" class="btn btn-sm btn-warning" onclick="openReturnModal('rd')"><i class="bi bi-arrow-return-left"></i> Return PO</button>
        @endif

        @if($purchaseOrder->status === 'routed_to_rd' && auth()->user()->hasAnyRole(['admin', 'regional-director', 'rd-staff']))
        <form action="{{ route('procurement.purchase-orders.receive', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-box-arrow-in-down"></i> Receive PO</button>
        </form>
        @endif

        @if($purchaseOrder->status === 'received_by_rd' && auth()->user()->hasAnyRole(['admin', 'regional-director']))
        <button type="button" class="btn btn-sm btn-success" onclick="openApproveModal('{{ route('procurement.purchase-orders.approve-rd', $purchaseOrder->id) }}')"><i class="bi bi-check-circle"></i> Approve (RD/ARD)</button>
        <button type="button" class="btn btn-sm btn-warning" onclick="openReturnModal('rd')"><i class="bi bi-arrow-return-left"></i> Return PO</button>
        @endif
        @if(in_array($purchaseOrder->status, ['rd_approved', 'ard_approved']) && auth()->user()->hasAnyRole(['admin', 'supply-officer']))
        <form action="{{ route('procurement.purchase-orders.send', $purchaseOrder->id) }}" method="POST" class="d-inline">@csrf
            <button type="submit" class="btn btn-sm btn-info text-white"><i class="bi bi-send"></i> Send to Supplier</button>
        </form>
        @endif
        @if(in_array($purchaseOrder->status, ['sent','partially_delivered']))
        <a href="{{ route('procurement.deliveries.create', ['po_id'=>$purchaseOrder->id]) }}" class="btn btn-sm btn-success"><i class="bi bi-truck"></i> Receive Delivery</a>
        @endif
        @if(!in_array($purchaseOrder->status, ['delivered','cancelled']) && auth()->user()->hasAnyRole(['admin', 'supply-officer']))
        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal"><i class="bi bi-x-circle"></i> Cancel</button>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">PO Details</h5></div>
            <div class="card-body p-0">
                @foreach([
                    ['PO Number', $purchaseOrder->po_number],
                    ['Supplier', $purchaseOrder->supplier?->name],
                    ['PO Date', $purchaseOrder->po_date?->format('M d, Y')],
                    ['Delivery Date', $purchaseOrder->delivery_date?->format('M d, Y') ?? 'Not set'],
                    ['Payment Terms', $purchaseOrder->payment_terms ?? '—'],
                    ['Delivery Address', $purchaseOrder->delivery_address ?? '—'],
                    ['Remarks', $purchaseOrder->notes ?? '—'],
                    ['Created By', $purchaseOrder->creator?->name],
                ] as [$l, $v])
                <div class="d-flex justify-content-between px-4 py-2" style="border-bottom:1px solid var(--border)">
                    <span style="font-size:12px;color:var(--text-muted)">{{ $l }}</span>
                    <span style="font-size:13px;font-weight:500;text-align:right;max-width:200px">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
        {{-- Amount Summary --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Subtotal</span><span>₱{{ number_format($purchaseOrder->subtotal, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Tax ({{ $purchaseOrder->tax_rate }}%)</span><span>₱{{ number_format($purchaseOrder->tax_amount, 2) }}</span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between"><span class="fw-bold">Total Amount</span><span class="fw-bold text-success" style="font-size:18px">₱{{ number_format($purchaseOrder->total_amount, 2) }}</span></div>
            </div>
        </div>
        {{-- Workflow Progress --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="fw-semibold">PO Workflow Progress</span><span class="fw-bold text-success">{{ $purchaseOrder->workflow_progress }}%</span></div>
                <div class="progress" style="height:10px;border-radius:10px">
                    <div class="progress-bar bg-success" style="width:{{ $purchaseOrder->workflow_progress }}%"></div>
                </div>
            </div>
        </div>

        {{-- Approval History --}}
        @if($purchaseOrder->approvals->count())
        <div class="card mt-4">
            <div class="card-header"><h5 class="card-title">Approval History</h5></div>
            <div class="list-group list-group-flush">
                @foreach($purchaseOrder->approvals as $approval)
                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                    <div>
                        <div class="fw-bold" style="font-size:13px">{{ $approval->approver?->name }} <span class="text-muted fw-normal">({{ $approval->level_label }})</span></div>
                        @if(in_array($approval->action, ['routed', 'sent', 'received', 'legacy_migrated']))
                        <div style="font-size:12px;color:var(--text-muted); margin-top:2px">{{ $approval->remarks }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                            {!! $approval->action_badge !!}
                            @if(!empty($approval->remarks) && in_array($approval->action, ['approved', 'returned']))
                            <button type="button" class="btn btn-sm btn-link p-0 text-muted" data-bs-toggle="popover" data-bs-trigger="focus" title="Remarks" data-bs-content="{{ $approval->remarks }}">
                                <i class="bi bi-info-circle-fill"></i>
                            </button>
                            @endif
                        </div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $approval->acted_at->format('M d, H:i') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">PO Attachment</h5></div>
            <div class="card-body text-center p-5">
                @if($purchaseOrder->attachment)
                    <i class="bi bi-file-earmark-check text-success mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold mb-2">Purchase Order Document</h5>
                    <p class="text-muted mb-4">View or download the attached Purchase Order document.</p>
                    <a href="{{ $purchaseOrder->attachment_url }}" target="_blank" class="btn btn-primary px-4 rounded-pill shadow-sm">
                        <i class="bi bi-box-arrow-up-right me-2"></i> View Attachment
                    </a>
                @else
                    <i class="bi bi-file-earmark-x text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold mb-2 text-muted">No Attachment</h5>
                    <p class="text-muted mb-0">This Purchase Order does not have a document attached.</p>
                @endif
            </div>
        </div>

        {{-- Deliveries --}}
        @if($purchaseOrder->deliveries->count())
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="bi bi-truck text-success me-2"></i>Delivery Records</h5></div>
            <div class="card-body p-0">
                @foreach($purchaseOrder->deliveries as $del)
                <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid var(--border)">
                    <div class="flex-grow-1">
                        <a href="{{ route('procurement.deliveries.show', $del->id) }}" class="fw-semibold text-decoration-none" style="font-size:13px;color:var(--primary)">{{ $del->grn_number }}</a>
                        <div style="font-size:11px;color:var(--text-muted)">DR#: {{ $del->dr_number ?? 'N/A' }} · {{ $del->delivery_date?->format('M d, Y') }}</div>
                    </div>
                    {!! $del->status_badge !!}
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title text-danger">Cancel Purchase Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('procurement.purchase-orders.cancel', $purchaseOrder->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <label class="form-label">Cancellation Reason *</label>
                <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Back</button><button type="submit" class="btn btn-danger">Cancel PO</button></div>
        </form>
    </div></div>
</div>
{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-check-circle text-primary me-2"></i>Approve PO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Return Modal --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('procurement.purchase-orders.return', $purchaseOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-return-left text-warning me-2"></i>Return PO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Return To:</label>
                        <div id="returnOptions">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks *</label>
                        <textarea name="remarks" class="form-control" rows="3" required placeholder="State the reason for return..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Return PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Route Forward Modal --}}
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('procurement.purchase-orders.route-forward', $purchaseOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-right-circle text-info me-2"></i>Route PO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Route To:</label>
                        <div id="forwardOptions">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white">Route PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

    function openApproveModal(actionUrl) {
        document.getElementById('approveForm').action = actionUrl;
        new bootstrap.Modal(document.getElementById('approveModal')).show();
    }

    function openReturnModal(currentLevel) {
        let optionsHtml = '';
        if (currentLevel === 'budget' || currentLevel === 'accounting' || currentLevel === 'rd') {
            optionsHtml += `<div class="form-check">
                <input class="form-check-input" type="radio" name="return_to" id="rt_supply" value="supply" required>
                <label class="form-check-label" for="rt_supply">Supply Officer</label>
            </div>`;
        }
        if (currentLevel === 'accounting' || currentLevel === 'rd') {
            optionsHtml += `<div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="return_to" id="rt_budget" value="budget">
                <label class="form-check-label" for="rt_budget">Budget Officer</label>
            </div>`;
        }
        if (currentLevel === 'rd') {
            optionsHtml += `<div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="return_to" id="rt_accounting" value="accounting">
                <label class="form-check-label" for="rt_accounting">Accounting</label>
            </div>`;
        }
        
        document.getElementById('returnOptions').innerHTML = optionsHtml;
        new bootstrap.Modal(document.getElementById('returnModal')).show();
    }

    function openForwardModal(currentLevel) {
        let optionsHtml = '';
        if (currentLevel === 'supply') {
            optionsHtml += `<div class="form-check">
                <input class="form-check-input" type="radio" name="forward_to" id="fw_budget" value="budget" required checked>
                <label class="form-check-label" for="fw_budget">Budget Officer</label>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="forward_to" id="fw_accounting" value="accounting">
                <label class="form-check-label" for="fw_accounting">Accounting</label>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="forward_to" id="fw_rd" value="rd">
                <label class="form-check-label" for="fw_rd">RD/ARD</label>
            </div>`;
        }
        if (currentLevel === 'budget') {
            optionsHtml += `<div class="form-check">
                <input class="form-check-input" type="radio" name="forward_to" id="fw_accounting" value="accounting" required checked>
                <label class="form-check-label" for="fw_accounting">Accounting</label>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="forward_to" id="fw_rd" value="rd">
                <label class="form-check-label" for="fw_rd">RD/ARD</label>
            </div>`;
        }
        if (currentLevel === 'accounting') {
            optionsHtml += `<div class="form-check">
                <input class="form-check-input" type="radio" name="forward_to" id="fw_rd" value="rd" required checked>
                <label class="form-check-label" for="fw_rd">RD/ARD</label>
            </div>`;
        }
        
        document.getElementById('forwardOptions').innerHTML = optionsHtml;
        new bootstrap.Modal(document.getElementById('forwardModal')).show();
    }
</script>
@endpush
