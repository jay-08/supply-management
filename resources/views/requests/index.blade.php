@extends('layouts.app')
@section('title', 'Supply Requests')
@section('breadcrumb')
    <li class="breadcrumb-item active">Supply Requests</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">Supply Requests</h1>
        <p class="page-subtitle">Submit and track supply requests.</p>
    </div>
    <a href="{{ route('requests.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Request
    </a>
</div>

{{-- Status Tabs --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    @foreach([''=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','issued'=>'Issued','claimed'=>'Claimed','cancelled'=>'Cancelled'] as $val=>$label)
    <a href="{{ route('requests.index', array_merge(request()->query(), ['status'=>$val])) }}"
       class="btn btn-sm {{ request('status')===$val ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="card">
    <div class="card-body p-0">
        @if($requests->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Request #</th><th>Requester</th><th>Department</th><th>Items</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td><a href="{{ route('requests.show', $req->id) }}" class="fw-bold text-decoration-none" style="color:var(--primary)">{{ $req->request_number }}</a></td>
                        <td style="font-size:13px">{{ $req->requester_name }}</td>
                        <td><span class="badge bg-light text-dark">{{ $req->department?->name }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $req->items->count() }}</span></td>
                        <td>{!! $req->status_badge !!}</td>
                        <td style="font-size:11px;color:var(--text-muted)">{{ $req->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('requests.show', $req->id) }}" class="btn btn-sm btn-light" style="border-radius:6px" title="View"><i class="bi bi-eye"></i></a>
                            @if($req->status === 'pending' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('supply-officer')))
                                <a href="{{ route('requests.show', $req->id) }}" class="btn btn-sm btn-success" style="border-radius:6px" title="Approve"><i class="bi bi-check-lg"></i></a>
                            @endif
                            @role('admin')
                            <form action="{{ route('requests.destroy', $req->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger" style="border-radius:6px" title="Delete" data-confirm="Are you sure you want to delete request {{ $req->request_number }}? This cannot be undone.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $requests->links() }}</div>
        @else
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5>No requests found</h5>
            <a href="{{ route('requests.create') }}" class="btn btn-primary mt-2"><i class="bi bi-plus-lg"></i> Create Request</a>
        </div>
        @endif
    </div>
</div>
@endsection
