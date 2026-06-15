@extends('layouts.app')
@section('page-title', '⚙️ Paramètres')
@section('content')
<div class="row justify-content-center"><div class="col-md-10">
<div class="card card-modern"><div class="card-body">
<form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
<h5 class="mb-3">🏢 Paramétrage entreprise</h5>
<div class="row g-3 mb-4">
<div class="col-md-6"><label class="form-label">Nom société</label><input name="nom_magasin" class="form-control" value="{{ $settings['nom_magasin'] }}" required></div>
<div class="col-md-6"><label class="form-label">📱 Téléphone</label><input name="telephone" class="form-control" value="{{ $settings['telephone'] }}"></div>
<div class="col-12"><label class="form-label">📍 Adresse</label><input name="adresse" class="form-control" value="{{ $settings['adresse'] }}"></div>
<div class="col-md-4"><label class="form-label">📧 Email</label><input name="email" type="email" class="form-control" value="{{ $settings['email'] }}"></div>
<div class="col-md-4"><label class="form-label">IFU</label><input name="ifu" class="form-control" value="{{ $settings['ifu'] }}"></div>
<div class="col-md-4"><label class="form-label">RCCM</label><input name="rccm" class="form-control" value="{{ $settings['rccm'] }}"></div>
<div class="col-md-6"><label class="form-label">🖼️ Logo</label><input name="logo" type="file" class="form-control" accept="image/*"></div>
</div>
<h5 class="mb-3">⚙️ Paramétrage système</h5>
<div class="row g-3">
<div class="col-md-3"><label class="form-label">💱 Devise</label><input name="devise" class="form-control" value="{{ $settings['devise'] }}" required></div>
<div class="col-md-3"><label class="form-label">🌐 Langue</label><select name="langue" class="form-select"><option value="fr" {{ $settings['langue']==='fr'?'selected':'' }}>Français</option><option value="en" {{ $settings['langue']==='en'?'selected':'' }}>English</option></select></div>
<div class="col-md-3"><label class="form-label">🕐 Fuseau horaire</label><input name="timezone" class="form-control" value="{{ $settings['timezone'] }}"></div>
<div class="col-md-3"><label class="form-label">🖨️ Format ticket</label><select name="ticket_width" class="form-select"><option value="58" {{ $settings['ticket_width']=='58'?'selected':'' }}>58 mm</option><option value="80" {{ $settings['ticket_width']=='80'?'selected':'' }}>80 mm</option></select></div>
<div class="col-md-3"><label class="form-label">📊 TVA (%)</label><input name="tva" type="number" class="form-control" value="{{ $settings['tva'] }}" step="0.1"></div>
</div>
<button type="submit" class="btn btn-primary mt-4">💾 Enregistrer</button>
</form></div></div></div></div>
@endsection
