@extends('layouts.app')

@section('page-title', '✏️ Modifier Rôle')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-modern">
            <div class="card-body">
                <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">🔐 Permissions</label>
                        <div class="row">
                            @foreach($allPermissions as $key => $label)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" class="form-check-input"
                                           id="perm_{{ $key }}"
                                           {{ in_array($key, $role->permissions ?? []) || in_array('*', $role->permissions ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">✅ Enregistrer</button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
