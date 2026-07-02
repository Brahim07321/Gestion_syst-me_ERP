<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        $companyId = DB::table('companies')
            ->where('slug', 'default-company')
            ->value('id');

        if ($companyId) {
            DB::table('categories')->whereNull('company_id')->update(['company_id' => $companyId]);
            DB::table('products')->whereNull('company_id')->update(['company_id' => $companyId]);
            DB::table('customers')->whereNull('company_id')->update(['company_id' => $companyId]);
            DB::table('suppliers')->whereNull('company_id')->update(['company_id' => $companyId]);
        }
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};