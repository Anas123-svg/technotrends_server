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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaintReference');
            $table->string('complaintImage');
            $table->string('clientName');
            $table->string('clientPhone');
            $table->string('title');
            $table->text('description');
            $table->date('dueDate')->nullable();
            $table->foreignId('createdByAdmin')->nullable()->constrained('admin')->nullOnDelete();
            $table->foreignId('assignedHead')->nullable()->constrained('head')->nullOnDelete();
            $table->string('jcReference');
            $table->string('jcImage');
            $table->json('photos');
            $table->enum('priority', ['Low', 'Medium', 'High']);
            $table->text('remarks')->nullable();
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Closed'])->default('Pending');
            $table->timestamps();
        });
        Schema::create('complaints_assigned_to_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('user')->cascadeOnDelete();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
