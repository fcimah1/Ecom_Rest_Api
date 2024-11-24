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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->text('image');
            $table->enum('status', ['on', 'off'])->default('on');
            $table->enum('type', ['Hot Offer ', 'Best Sale', 'New Arrival', 'Featured', 'Sale', 'Home Page', 'Shop Page', 'Checkout Page', 'Order'])->default('Hot Offer ');
            $table->string('url')->nullable();
            $table->string('position')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
