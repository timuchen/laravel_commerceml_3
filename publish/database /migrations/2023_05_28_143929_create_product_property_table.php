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
        Schema::create('product_property', function (Blueprint $table) {
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('property_id')->unsigned();
            $table->bigInteger('property_value_id')->unsigned()->nullable();
            $table->foreign('product_id')->on('products')->references('id');
            $table->foreign('property_id')->on('properties')->references('id');
            $table->foreign('property_value_id')->on('property_values')->references('id');
            $table->string('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_property');
    }
};
