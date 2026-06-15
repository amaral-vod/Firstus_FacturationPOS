<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->enum('type', ['facture', 'devis', 'bon_commande', 'bon_livraison', 'ticket', 'facture_a4', 'avoir'])->default('facture');
            $table->foreignId('vente_id')->nullable()->constrained('ventes')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('sous_total', 12, 2)->default(0);
            $table->decimal('remise', 12, 2)->default(0);
            $table->decimal('tva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('statut', ['brouillon', 'valide', 'paye', 'annule', 'converti'])->default('brouillon');
            $table->date('date_echeance')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('imprime_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
