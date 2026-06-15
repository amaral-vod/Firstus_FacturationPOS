<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retour_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retour_id')->constrained('retours')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('detail_vente_id')->constrained('detail_ventes')->cascadeOnDelete();
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('total_ligne', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retour_details');
    }
};
