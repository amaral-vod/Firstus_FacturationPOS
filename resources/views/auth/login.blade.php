@extends('layouts.app')

@section('title', 'Connexion')

@section('styles')
<style>
    .account-card { cursor: pointer; transition: all .2s; border: 2px solid transparent; }
    .account-card:hover { border-color: var(--primary); background: #f8fafc; }
</style>
@endsection

@section('content')
<div class="row justify-content-center g-4">
    <div class="col-lg-{{ $demoMode ? '6' : '5' }} {{ $demoMode ? '' : 'mx-auto' }}">
        <div class="card card-modern shadow">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h1 class="display-6">🛒</h1>
                    <h3 class="fw-bold">Firstus POS</h3>
                    <p class="text-muted small">Gestion Commerciale & Facturation</p>
                    @if($demoMode)
                    <span class="badge bg-info text-dark">Mode démonstration</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">📧 Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">🔑 Mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control"
                               value="{{ $demoMode ? 'password' : '' }}" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">🚀 Se connecter</button>
                </form>
            </div>
        </div>
    </div>

    @if($demoMode)
    <div class="col-lg-6">
        <div class="card card-modern shadow h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0">🔐 Comptes utilisateurs (mot de passe : <code>password</code>)</h6>
            </div>
            <div class="card-body p-2" style="max-height:520px;overflow-y:auto">
                @forelse($accounts as $account)
                <div class="account-card p-3 mb-2 rounded"
                     onclick="fillLogin('{{ $account->email }}', 'password')">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $account->name }}</strong><br>
                            <small class="text-muted">{{ $account->email }}</small>
                        </div>
                        <span class="badge bg-primary">{{ $account->role?->name ?? 'Sans rôle' }}</span>
                    </div>
                    <small class="text-primary">👆 Cliquer pour remplir le formulaire</small>
                </div>
                @empty
                <p class="text-muted p-3">Aucun compte actif.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function fillLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    document.getElementById('email').focus();
}
</script>
@endsection
