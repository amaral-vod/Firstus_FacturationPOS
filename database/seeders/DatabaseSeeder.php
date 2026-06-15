<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Site;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('permissions.roles') as $slug => $permissions) {
            $names = [
                'super_admin' => 'Super Administrateur',
                'admin' => 'Administrateur',
                'caissier' => 'Caissier',
                'magasinier' => 'Magasinier',
                'comptable' => 'Comptable',
                'logisticien' => 'Logisticien',
            ];
            Role::updateOrCreate(['slug' => $slug], [
                'name' => $names[$slug] ?? ucfirst($slug),
                'description' => "Rôle {$slug}",
                'permissions' => $permissions,
            ]);
        }

        $site = Site::updateOrCreate(['code' => 'CTN'], [
            'name' => 'Boutique Cotonou',
            'city' => 'Cotonou',
            'address' => '123 Avenue du Commerce',
            'phone' => '+229 00 00 00 00',
            'is_active' => true,
            'is_default' => true,
        ]);

        Site::updateOrCreate(['code' => 'PNV'], ['name' => 'Boutique Porto-Novo', 'city' => 'Porto-Novo', 'is_active' => true]);
        Site::updateOrCreate(['code' => 'PRK'], ['name' => 'Boutique Parakou', 'city' => 'Parakou', 'is_active' => true]);

        $adminRole = Role::where('slug', 'super_admin')->first();

        User::updateOrCreate(['email' => 'admin@firstus.com'], [
            'name' => 'Super Admin Firstus',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'site_id' => $site->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'caissier@firstus.com'], [
            'name' => 'Caissier Demo',
            'password' => Hash::make('password'),
            'role_id' => Role::where('slug', 'caissier')->first()->id,
            'site_id' => $site->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'magasinier@firstus.com'], [
            'name' => 'Magasinier Demo',
            'password' => Hash::make('password'),
            'role_id' => Role::where('slug', 'magasinier')->first()->id,
            'site_id' => $site->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'comptable@firstus.com'], [
            'name' => 'Comptable Demo',
            'password' => Hash::make('password'),
            'role_id' => Role::where('slug', 'comptable')->first()->id,
            'site_id' => $site->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'logisticien@firstus.com'], [
            'name' => 'Logisticien Demo',
            'password' => Hash::make('password'),
            'role_id' => Role::where('slug', 'logisticien')->first()->id,
            'site_id' => $site->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'admin2@firstus.com'], [
            'name' => 'Administrateur Demo',
            'password' => Hash::make('password'),
            'role_id' => Role::where('slug', 'admin')->first()->id,
            'site_id' => $site->id,
            'is_active' => true,
        ]);

        Client::updateOrCreate(['code' => 'CLI-DEMO1'], [
            'name' => 'Client Démo SARL', 'phone' => '+229 90 00 00 01',
            'credit_limit' => 500000, 'balance_due' => 125000, 'site_id' => $site->id,
        ]);

        Fournisseur::updateOrCreate(['code' => 'FOU-DEMO1'], [
            'name' => 'Fournisseur Démo', 'phone' => '+229 90 00 00 02',
            'balance' => 85000, 'site_id' => $site->id,
        ]);

        $categories = [
            ['name' => 'Alimentation', 'slug' => 'alimentation'],
            ['name' => 'Boissons', 'slug' => 'boissons'],
            ['name' => 'Hygiène', 'slug' => 'hygiene'],
            ['name' => 'Électronique', 'slug' => 'electronique'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat + ['is_active' => true]);
        }

        $products = [
            ['name' => 'Riz 1kg', 'sku' => 'RIZ-001', 'price' => 500, 'cost' => 380, 'category' => 'alimentation', 'stock' => 100],
            ['name' => 'Huile 1L', 'sku' => 'HUI-001', 'price' => 1200, 'cost' => 950, 'category' => 'alimentation', 'stock' => 50],
            ['name' => 'Eau minérale 1.5L', 'sku' => 'EAU-001', 'price' => 300, 'cost' => 200, 'category' => 'boissons', 'stock' => 200],
            ['name' => 'Jus d\'orange 1L', 'sku' => 'JUS-001', 'price' => 800, 'cost' => 550, 'promo_price' => 650, 'category' => 'boissons', 'stock' => 30],
            ['name' => 'Savon', 'sku' => 'SAV-001', 'price' => 400, 'cost' => 280, 'category' => 'hygiene', 'stock' => 3],
            ['name' => 'Dentifrice', 'sku' => 'DEN-001', 'price' => 600, 'cost' => 420, 'category' => 'hygiene', 'stock' => 25],
            ['name' => 'Câble USB', 'sku' => 'CAB-001', 'price' => 1500, 'cost' => 900, 'category' => 'electronique', 'stock' => 15],
            ['name' => 'Écouteurs', 'sku' => 'ECO-001', 'price' => 3500, 'cost' => 2200, 'category' => 'electronique', 'stock' => 8],
        ];

        $adminUser = User::where('email', 'admin@firstus.com')->first();

        foreach ($products as $p) {
            $category = Category::where('slug', $p['category'])->first();
            $product = Product::updateOrCreate(['sku' => $p['sku']], [
                'name' => $p['name'],
                'category_id' => $category->id,
                'price' => $p['price'],
                'cost' => $p['cost'],
                'promo_price' => $p['promo_price'] ?? null,
                'is_active' => true,
            ]);

            if (! $product->stock) {
                $product->stock()->create(['quantity' => 0, 'min_quantity' => 5]);
            }

            StockService::adjust($product, $p['stock'], 'entree', 'SEED', 'Stock initial', $adminUser->id);
        }

        $settings = [
            'nom_magasin' => 'Firstus Facturation POS',
            'adresse' => '123 Avenue du Commerce, Cotonou',
            'telephone' => '+229 33 000 00 00',
            'email' => 'contact@firstus.com',
            'ifu' => 'IFU-123456789',
            'rccm' => 'RCCM RB/COT/21 B 12345',
            'devise' => 'FCFA',
            'langue' => 'fr',
            'timezone' => 'Africa/Porto-Novo',
            'ticket_width' => '80',
            'tva' => '18',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        NotificationService::alertStockFaible();
    }
}
