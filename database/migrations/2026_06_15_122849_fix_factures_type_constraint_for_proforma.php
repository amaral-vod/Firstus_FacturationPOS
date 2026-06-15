<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE factures DROP CONSTRAINT IF EXISTS factures_type_check');
        DB::statement("ALTER TABLE factures ADD CONSTRAINT factures_type_check CHECK (type IN (
            'facture', 'devis', 'bon_commande', 'bon_livraison',
            'ticket', 'facture_a4', 'avoir', 'proforma'
        ))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE factures DROP CONSTRAINT IF EXISTS factures_type_check');
        DB::statement("ALTER TABLE factures ADD CONSTRAINT factures_type_check CHECK (type IN (
            'facture', 'devis', 'bon_commande', 'bon_livraison',
            'ticket', 'facture_a4', 'avoir'
        ))");
    }
};
