<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('sous_total', 12, 2)->default(0);
            $table->decimal('remise', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('monnaie', 12, 2)->default(0);
            $table->enum('statut', ['complete', 'annulee', 'partiellement_retournee', 'retournee'])->default('complete');
            $table->string('mode_paiement')->default('especes');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
