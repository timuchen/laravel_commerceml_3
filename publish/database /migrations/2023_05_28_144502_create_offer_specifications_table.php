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
        Schema::create('offer_specifications', function (Blueprint $table) {
            $table->bigInteger('offer_id')->unsigned();
            $table->bigInteger('specification_id')->unsigned();
            $table->string('value')->nullable();
            $table->foreign('offer_id')->on('offers')->references('id');
            $table->foreign('specification_id')->on('specifications')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_specifications');
    }
};
