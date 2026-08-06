@extends('layouts.app')
@section('title', 'Database Backup & Restore')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active">Database Backup</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-database-fill-gear text-primary me-2"></i>Database Backup & Restore</h1>
        <p class="page-subtitle">Export SQL dumps or JSON data archives to safeguard your system records.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('backups.download-sql') }}" class="btn btn-primary px-3 shadow-sm rounded-pill">
            <i class="bi bi-download me-1"></i> Download SQL Backup (.sql)
        </a>
        <a href="{{ route('backups.download-json') }}" class="btn btn-outline-primary px-3 rounded-pill">
            <i class="bi bi-filetype-json me-1"></i> Export JSON Data (.json)
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 p-4 border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(37,99,235,0.08) 0%, rgba(124,58,237,0.08) 100%); border-radius: 16px;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.4rem;">
                    <i class="bi bi-table"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">{{ count($tableStats) }}</h3>
                    <div class="text-muted small">Database Tables</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 p-4 border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(5,150,105,0.08) 100%); border-radius: 16px;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.4rem;">
                    <i class="bi bi-hdd-stack"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">{{ number_format($totalRecords) }}</h3>
                    <div class="text-muted small">Total Data Rows</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 p-4 border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(245,158,11,0.08) 0%, rgba(217,119,6,0.08) 100%); border-radius: 16px;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.4rem;">
                    <i class="bi bi-cpu"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">{{ $dbSizeMb > 0 ? $dbSizeMb . ' MB' : 'Active' }}</h3>
                    <div class="text-muted small">Database Storage Size</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- TABLE BREAKDOWN --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-list-columns me-2"></i>Database Tables Breakdown</h5>
                <span class="badge bg-primary-subtle text-primary">{{ count($tableStats) }} Tables</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Table Name</th>
                            <th class="text-end">Record Count</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tableStats as $index => $stat)
                        <tr>
                            <td><span class="text-muted small">{{ $index + 1 }}</span></td>
                            <td class="fw-semibold"><code>{{ $stat['name'] }}</code></td>
                            <td class="text-end fw-bold">{{ number_format($stat['count']) }}</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success">Healthy</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- RESTORE BACKUP CARD --}}
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-upload text-warning me-2"></i>Restore Database Backup</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Upload a previously downloaded <code>.sql</code> or <code>.json</code> backup file to restore your database tables.</p>

                <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" style="border-radius: 12px;">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1" style="font-size: 1.2rem;"></i>
                    <div style="font-size: 12px;">
                        <strong>Important Caution:</strong> Restoring a backup file will update and replace existing data tables. Ensure you create a current backup first!
                    </div>
                </div>

                <form action="{{ route('backups.restore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Backup File (.sql or .json)</label>
                        <input type="file" name="backup_file" class="form-control" accept=".sql,.json,.txt" required>
                        <div class="form-text">Maximum file size: 20 MB.</div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill" onclick="return confirm('Are you sure you want to restore the database from this backup file?')">
                        <i class="bi bi-arrow-clockwise me-1"></i> Start Database Restore
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-shield-check text-success me-2"></i>Backup Best Practices</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Download a <strong>SQL backup</strong> weekly or before major data changes.</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Export <strong>JSON archives</strong> for lightweight data inspection or migration.</li>
                    <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i>Store backup files securely in an offsite cloud drive.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
