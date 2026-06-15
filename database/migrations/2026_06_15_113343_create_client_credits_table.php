<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vente_id')->nullable()->constrained('ventes')->nullOnDelete();
            $table->foreignId('facture_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('montant', 12, 2);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->date('date_echeance');
            $table->enum('statut', ['en_cours', 'partiel', 'paye', 'en_retard'])->default('en_cours');
            $table->timestamp('relance_le')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_credits');
    }
};
