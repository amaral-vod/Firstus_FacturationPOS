@extends('layouts.app')
@section('page-title', '👥 Groupes utilisateurs')
@section('content')
<div class="row g-4">@foreach($groups as $group)
<div class="col-md-4"><div class="card card-modern h-100"><div class="card-body">
<h5>{{ $group->name }}</h5><p class="text-muted small">{{ $group->description }}</p>
<span class="badge bg-secondary">{{ $group->users_count }} membre(s)</span>
<form method="POST" action="{{ route('admin.groups.update',$group) }}" class="mt-3">@csrf @method('PUT')
<input name="name" class="form-control form-control-sm mb-2" value="{{ $group->name }}">
<div class="small" style="max-height:120px;overflow:auto">@foreach($allPermissions as $k=>$l)
<div class="form-check"><input type="checkbox" name="permissions[]" value="{{ $k }}" class="form-check-input" id="g{{ $group->id }}_{{ $k }}"
{{ in_array($k,$group->permissions??[])?'checked':'' }}><label class="form-check-label" for="g{{ $group->id }}_{{ $k }}">{{ $l }}</label></div>@endforeach</div>
<button class="btn btn-sm btn-primary mt-2">Sauvegarder</button></form>
<form method="POST" action="{{ route('admin.groups.destroy',$group) }}" class="mt-1" onsubmit="return confirm('Supprimer?')">@csrf @method('DELETE')
<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></div></div></div>@endforeach
<div class="col-md-4"><div class="card card-modern"><div class="card-header bg-primary text-white">➕ Nouveau groupe</div><div class="card-body">
<form method="POST" action="{{ route('admin.groups.store') }}">@csrf
<input name="name" class="form-control mb-2" placeholder="Nom du groupe" required>
<textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>
<button class="btn btn-primary w-100">Créer</button></form></div></div></div></div>
@endsection
