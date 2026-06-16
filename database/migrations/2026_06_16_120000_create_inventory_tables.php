<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('inventory_date');
            $table->enum('status', ['brouillon', 'valide', 'annule'])->default('brouillon');
            $table->text('notes')->nullable();
            $table->integer('total_theoretical_qty')->default(0);
            $table->integer('total_counted_qty')->default(0);
            $table->integer('total_variance_qty')->default(0);
            $table->decimal('total_theoretical_value', 14, 2)->default(0);
            $table->decimal('total_counted_value', 14, 2)->default(0);
            $table->decimal('total_variance_value', 14, 2)->default(0);
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('theoretical_qty')->default(0);
            $table->integer('counted_qty')->default(0);
            $table->integer('variance_qty')->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('variance_value', 14, 2)->default(0);
            $table->enum('variance_reason', [
                'erreur_saisie', 'casse', 'vol', 'perte', 'autre',
            ])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['inventory_session_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_lines');
        Schema::dropIfExists('inventory_sessions');
    }
};
