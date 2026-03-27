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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('Category_ID')->constrained('categories')->cascadeOnDelete();
            $table->string('code');
            $table->string('Referonce');
            $table->string('Designation');
            $table->string('prace_bay');
            $table->string('prace_sell');
            $table->string('Quantite');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
