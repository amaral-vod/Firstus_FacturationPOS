@extends('layouts.app')

@section('page-title', '📜 Journal des Activités')

@section('content')
<div class="card card-modern">
    <div class="card-header bg-white">
        <form class="row g-2">
            <div class="col-md-3">
                <select name="module" class="form-select form-select-sm">
                    <option value="">Tous modules</option>
                    @foreach($modules as $m)<option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ $m }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="action" class="form-select form-select-sm">
                    <option value="">Toutes actions</option>
                    @foreach($actions as $a)<option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary w-100">🔍 Filtrer</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Utilisateur</th><th>Module</th><th>Action</th><th>Description</th><th>IP</th></tr>
            </thead>
            <tbody>
                @foreach($activites as $a)
                <tr>
                    <td><small>{{ $a->created_at->format('d/m/Y H:i:s') }}</small></td>
                    <td>{{ $a->user?->name ?? '—' }}</td>
                    <td><span class="badge bg-secondary">{{ $a->module }}</span></td>
                    <td><span class="badge bg-info">{{ $a->action }}</span></td>
                    <td>{{ $a->description }}</td>
                    <td><small class="text-muted">{{ $a->ip_address }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $activites->links() }}</div>
</div>
@endsection
