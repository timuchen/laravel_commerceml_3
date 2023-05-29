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
            $table->string('name');
            $table->string('article');
            $table->text('description');
            $table->bigInteger('group_id')->nullable();
            $table->bigInteger('catalog_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->uuid('accounting_id')->comment('Код из 1с');
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
