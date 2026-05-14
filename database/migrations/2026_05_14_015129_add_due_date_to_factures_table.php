<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('date_facture');
        });
    }
    
    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
};
