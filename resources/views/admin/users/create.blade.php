@extends('layouts.app')

@section('page-title', '➕ Nouvel Utilisateur')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-modern">
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    @include('admin.users._form')
                    <button type="submit" class="btn btn-primary">✅ Créer</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
