<div class="mb-3">
    <label class="form-label">👤 Nom</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">📧 Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">📱 Téléphone</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">🔑 Mot de passe {{ isset($user) ? '(laisser vide pour ne pas changer)' : '' }}</label>
    <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
    <input type="password" name="password_confirmation" class="form-control mt-2" placeholder="Confirmer">
</div>
<div class="mb-3">
    <label class="form-label">🔐 Rôle</label>
    <select name="role_id" class="form-select" required>
        @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>
                {{ $role->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
           {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">✅ Compte actif</label>
</div>
