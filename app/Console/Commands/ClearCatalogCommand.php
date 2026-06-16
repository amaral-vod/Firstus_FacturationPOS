<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearCatalogCommand extends Command
{
    protected $signature = 'catalog:clear {--force : Sans confirmation}';

    protected $description = 'Vide fournisseurs, catégories, produits, stock, clients et crédits';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Vider fournisseurs, catégories, produits, stock, clients et crédits ?')) {
            $this->info('Annulé.');

            return self::SUCCESS;
        }

        $tables = [
            'client_credits',
            'fournisseur_reglements',
            'stock_movements',
            'stocks',
            'retour_details',
            'detail_ventes',
            'facture_details',
            'products',
            'clients',
            'fournisseurs',
            'categories',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            DB::table($table)->truncate();
            $this->line("  ✓ {$table}");
        }

        DB::table('ventes')->update([
            'client_id' => null,
            'sous_total' => 0,
            'remise' => 0,
            'total' => 0,
            'montant_paye' => 0,
            'monnaie' => 0,
        ]);

        DB::table('factures')->update(['client_id' => null]);

        DB::table('retours')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info('✅ Tables vidées : fournisseurs, catégories, produits, stock, clients, crédits.');

        return self::SUCCESS;
    }
}
