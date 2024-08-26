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
        Schema::create('payment_invoice_receiveds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('received_invoice_id')->constrained('received_invoices');
            $table->float('paye');
            $table->date('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_invoice_receiveds');
    }
};
