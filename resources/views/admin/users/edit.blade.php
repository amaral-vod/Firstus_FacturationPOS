@extends('layouts.app')

@section('page-title', '✏️ Modifier Utilisateur')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-modern">
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf @method('PUT')
                    @include('admin.users._form', ['user' => $user])
                    <button type="submit" class="btn btn-primary">✅ Mettre à jour</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
