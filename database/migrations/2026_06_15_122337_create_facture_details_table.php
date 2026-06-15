<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FACTURE_TYPES = [
        'facture', 'devis', 'bon_commande', 'bon_livraison',
        'ticket', 'facture_a4', 'avoir', 'proforma',
    ];

    public function up(): void
    {
        Schema::create('facture_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('designation');
            $table->integer('quantite')->default(1);
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('total_ligne', 12, 2);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->string('format_papier')->default('auto')->after('type');
            $table->string('client_nom')->nullable()->after('client_id');
            $table->string('client_adresse')->nullable()->after('client_nom');
            $table->string('client_telephone')->nullable()->after('client_adresse');
        });

        $this->ensureFactureTypeSupportsProforma();
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['format_papier', 'client_nom', 'client_adresse', 'client_telephone']);
        });
        Schema::dropIfExists('facture_details');
    }

    private function ensureFactureTypeSupportsProforma(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $typesList = implode(', ', array_map(fn ($t) => "'{$t}'", self::FACTURE_TYPES));

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE factures ALTER COLUMN type TYPE VARCHAR(30) USING type::text');
            DB::statement('ALTER TABLE factures DROP CONSTRAINT IF EXISTS factures_type_check');
            DB::statement("ALTER TABLE factures ADD CONSTRAINT factures_type_check CHECK (type IN ({$typesList}))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE factures MODIFY COLUMN type ENUM({$typesList}) NOT NULL DEFAULT 'facture'");
        }
    }
};
