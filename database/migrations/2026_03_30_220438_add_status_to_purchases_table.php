<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



return new class extends Migration
{
    /**
     * Run the migrations.
     */
    
    public function up(): void
    {
        if (!Schema::hasColumn('purchases', 'status')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('status')->default('en attente');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchases', 'status')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
