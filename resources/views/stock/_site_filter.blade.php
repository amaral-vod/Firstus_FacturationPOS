@if(isset($sites) && $sites->count() > 1)
<form method="GET" class="d-flex align-items-center gap-2 mb-3">
    @foreach(request()->except('site_id', 'page') as $key => $value)
        @if(is_array($value))
            @foreach($value as $v)
            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <label class="form-label mb-0 small text-muted">🏪 Site</label>
    <select name="site_id" class="form-select form-select-sm" style="max-width:220px" onchange="this.form.submit()">
        @foreach($sites as $site)
        <option value="{{ $site->id }}" @selected(($siteId ?? null) == $site->id)>{{ $site->name }}</option>
        @endforeach
    </select>
</form>
@endif
