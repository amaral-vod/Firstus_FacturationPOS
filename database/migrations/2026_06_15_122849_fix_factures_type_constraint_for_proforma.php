<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        $typesList = "'facture', 'devis', 'bon_commande', 'bon_livraison', 'ticket', 'facture_a4', 'avoir', 'proforma'";

        DB::statement('ALTER TABLE factures DROP CONSTRAINT IF EXISTS factures_type_check');
        DB::statement("ALTER TABLE factures ADD CONSTRAINT factures_type_check CHECK (type IN ({$typesList}))");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        $typesList = "'facture', 'devis', 'bon_commande', 'bon_livraison', 'ticket', 'facture_a4', 'avoir'";

        DB::statement('ALTER TABLE factures DROP CONSTRAINT IF EXISTS factures_type_check');
        DB::statement("ALTER TABLE factures ADD CONSTRAINT factures_type_check CHECK (type IN ({$typesList}))");
    }
};
