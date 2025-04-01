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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoiceReference');
            $table->string('invoiceImage');
            $table->decimal('amount', 10, 2);
            $table->enum('paymentTerms', ['Cash', 'Credit']);
            $table->string('creditDays')->nullable();
            $table->date('dueDate')->nullable();
            $table->foreignId('linkedProject')->constrained('projects')->onDelete('cascade');
            $table->enum('status', ['Paid', 'Unpaid', 'In Progress', 'Overdue', 'Cancelled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
