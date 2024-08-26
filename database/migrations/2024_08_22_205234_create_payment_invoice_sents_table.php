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
        Schema::create('payment_invoice_sents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->float('paye');
            $table->date('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_invoice_sents');
    }
};
