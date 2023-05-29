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
        Schema::create('product_requisite', function (Blueprint $table) {
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('requisite_id')->unsigned();
            $table->foreign('product_id')->on('products')->references('id');
            $table->foreign('requisite_id')->on('requisites')->references('id');
            $table->string('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_requisite');
    }
};
