<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisse_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('fond_initial', 12, 2)->default(0);
            $table->decimal('fond_theorique', 12, 2)->nullable();
            $table->decimal('fond_reel', 12, 2)->nullable();
            $table->decimal('ecart', 12, 2)->nullable();
            $table->enum('ecart_type', ['aucun', 'surplus', 'manquant'])->default('aucun');
            $table->enum('statut', ['ouverte', 'fermee'])->default('ouverte');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse_sessions');
    }
};
