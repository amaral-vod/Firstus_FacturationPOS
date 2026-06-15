<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('role_id')->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('is_active');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->boolean('two_factor_enabled')->default(false)->after('locked_until');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->nullable()->after('price');
        });

        Schema::table('retours', function (Blueprint $table) {
            $table->enum('motif_type', ['client', 'fournisseur', 'defectueux', 'perime'])->default('client')->after('type');
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('caisse_session_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->foreignId('site_id')->nullable()->after('caisse_session_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropConstrainedForeignId('caisse_session_id');
            $table->dropConstrainedForeignId('site_id');
        });
        Schema::table('retours', function (Blueprint $table) {
            $table->dropColumn('motif_type');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('site_id');
            $table->dropColumn(['failed_login_attempts', 'locked_until', 'two_factor_enabled']);
        });
    }
};
