<?php

use App\Models\Site;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });

        $defaultSiteId = Site::where('is_default', true)->value('id')
            ?? Site::where('is_active', true)->value('id')
            ?? Site::query()->value('id');

        if ($defaultSiteId) {
            DB::table('stocks')->whereNull('site_id')->update(['site_id' => $defaultSiteId]);
        }

        Schema::table('stocks', function (Blueprint $table) {
            $table->unique(['product_id', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'site_id']);
            $table->dropConstrainedForeignId('site_id');
        });
    }
};
