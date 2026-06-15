@extends('layouts.app')
@section('page-title', '🔑 Permissions')
@section('content')
<div class="card card-modern"><div class="card-body p-0"><table class="table mb-0"><thead><tr><th>Permission</th>@foreach($roles as $role)<th>{{ $role->name }}</th>@endforeach</tr></thead>
<tbody>@foreach($permissions as $key=>$label)<tr><td>{{ $label }}<br><small class="text-muted">{{ $key }}</small></td>
@foreach($roles as $role)<td class="text-center">{!! ($role->hasPermission($key))? '✅' : '—' !!}</td>@endforeach</tr>@endforeach</tbody></table></div></div>
<p class="text-muted small mt-2">Modifiez les permissions via <a href="{{ route('admin.roles.index') }}">Rôles</a> ou <a href="{{ route('admin.groups.index') }}">Groupes</a>.</p>
@endsection
