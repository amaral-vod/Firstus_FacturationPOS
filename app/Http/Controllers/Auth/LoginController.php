<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\SecurityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const LOCK_MINUTES = 15;

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $accounts = User::with('role')->where('is_active', true)->orderBy('name')->get();

        $base = rtrim(config('app.url'), '/');

        $links = [
            ['label' => '📊 Tableau de bord', 'url' => "{$base}/dashboard"],
            ['label' => '💰 POS / Caisse', 'url' => "{$base}/caisse"],
            ['label' => '🏧 Ouverture/Fermeture caisse', 'url' => "{$base}/caisse/sessions"],
            ['label' => '🧾 Historique ventes', 'url' => "{$base}/caisse/historique"],
            ['label' => '📄 Facturation', 'url' => "{$base}/facturation"],
            ['label' => '🤝 Clients', 'url' => "{$base}/clients"],
            ['label' => '💳 Crédits clients', 'url' => "{$base}/clients/credits"],
            ['label' => '📦 Stock', 'url' => "{$base}/stock"],
            ['label' => '📜 Mouvements stock', 'url' => "{$base}/stock/mouvements"],
            ['label' => '🏷️ Produits', 'url' => "{$base}/admin/products"],
            ['label' => '📂 Catégories', 'url' => "{$base}/admin/categories"],
            ['label' => '🏭 Fournisseurs', 'url' => "{$base}/fournisseurs"],
            ['label' => '💸 Règlements fournisseurs', 'url' => "{$base}/fournisseurs/reglements"],
            ['label' => '↩️ Retours', 'url' => "{$base}/retours"],
            ['label' => '🚫 Annulations', 'url' => "{$base}/annulations"],
            ['label' => '📈 Rapports & BI', 'url' => "{$base}/rapports"],
            ['label' => '📜 Journal / Audit', 'url' => "{$base}/journal"],
            ['label' => '🔔 Notifications', 'url' => "{$base}/notifications"],
            ['label' => '👥 Utilisateurs', 'url' => "{$base}/admin/users"],
            ['label' => '🔐 Rôles', 'url' => "{$base}/admin/roles"],
            ['label' => '🔑 Permissions', 'url' => "{$base}/admin/permissions"],
            ['label' => '👥 Groupes', 'url' => "{$base}/admin/groups"],
            ['label' => '📜 Historique connexions', 'url' => "{$base}/admin/connexions"],
            ['label' => '🛡️ Sécurité', 'url' => "{$base}/securite"],
            ['label' => '⚙️ Paramètres', 'url' => "{$base}/parametres"],
        ];

        return view('auth.login', compact('accounts', 'links', 'base'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user?->isLocked()) {
            return back()->with('error', '🔒 Compte temporairement bloqué. Réessayez plus tard.');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $this->recordLogin($credentials['email'], $user->id, false);

                return back()->with('error', '🚫 Votre compte est désactivé.');
            }

            $user->update(['failed_login_attempts' => 0, 'locked_until' => null]);
            $request->session()->regenerate();

            $history = $this->recordLogin($credentials['email'], $user->id, true);
            $request->session()->put('login_history_id', $history->id);

            ActivityLogger::log('connexion', 'auth', "Connexion de {$user->name}");
            SecurityLog::create([
                'user_id' => $user->id,
                'action' => 'connexion',
                'ip_address' => $request->ip(),
                'description' => "Connexion réussie — {$user->email}",
            ]);

            return redirect()->intended(route('dashboard'));
        }

        $this->recordLogin($credentials['email'], $user?->id, false);

        if ($user) {
            $attempts = $user->failed_login_attempts + 1;
            $updates = ['failed_login_attempts' => $attempts];
            if ($attempts >= self::MAX_ATTEMPTS) {
                $updates['locked_until'] = now()->addMinutes(self::LOCK_MINUTES);
                ActivityLogger::log('blocage', 'securite', "Compte bloqué après {$attempts} tentatives — {$user->email}");
            }
            $user->update($updates);
        }

        SecurityLog::create([
            'user_id' => $user?->id,
            'action' => 'echec_connexion',
            'ip_address' => $request->ip(),
            'description' => "Tentative échouée — {$credentials['email']}",
        ]);

        return back()->with('error', '❌ Identifiants incorrects.');
    }

    public function logout(Request $request)
    {
        if ($historyId = $request->session()->get('login_history_id')) {
            LoginHistory::where('id', $historyId)->update(['logged_out_at' => now()]);
        }

        if (Auth::check()) {
            ActivityLogger::log('deconnexion', 'auth', 'Déconnexion de '.Auth::user()->name);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', '👋 À bientôt !');
    }

    private function recordLogin(string $email, ?int $userId, bool $success): LoginHistory
    {
        return LoginHistory::create([
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => $success,
            'logged_in_at' => $success ? now() : null,
        ]);
    }
}
