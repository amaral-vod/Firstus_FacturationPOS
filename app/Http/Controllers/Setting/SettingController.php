<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $keys = [
            'nom_magasin', 'adresse', 'telephone', 'email', 'ifu', 'rccm', 'logo',
            'devise', 'langue', 'timezone', 'ticket_width', 'tva',
        ];
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = Setting::get($key, match ($key) {
                'nom_magasin' => 'Firstus POS',
                'devise' => 'FCFA',
                'langue' => 'fr',
                'timezone' => 'Africa/Porto-Novo',
                'ticket_width' => '80',
                'tva' => '0',
                default => '',
            });
        }

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nom_magasin' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'ifu' => 'nullable|string|max:50',
            'rccm' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'devise' => 'required|string|max:10',
            'langue' => 'required|in:fr,en',
            'timezone' => 'required|string',
            'ticket_width' => 'required|in:58,80',
            'tva' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            Setting::set('logo', $path);
        }
        unset($data['logo']);

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value);
        }

        ActivityLogger::log('modification', 'parametres', 'Mise à jour des paramètres');

        return back()->with('success', '⚙️ Paramètres enregistrés.');
    }
}
