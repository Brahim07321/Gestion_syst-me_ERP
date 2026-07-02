<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        $defaultCompanyId = DB::table('companies')
            ->where('slug', 'default-company')
            ->value('id') ?? DB::table('companies')->value('id');

        if ($defaultCompanyId) {
            DB::table('purchases')->whereNull('company_id')->update([
                'company_id' => $defaultCompanyId,
            ]);

            DB::table('factures')->whereNull('company_id')->update([
                'company_id' => $defaultCompanyId,
            ]);

            DB::table('expenses')->whereNull('company_id')->update([
                'company_id' => $defaultCompanyId,
            ]);

            DB::table('stock_movements')->whereNull('company_id')->update([
                'company_id' => $defaultCompanyId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};