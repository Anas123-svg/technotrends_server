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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('poNumber');
            $table->string('poImage');
            $table->string('clientName');
            $table->string('clientPhone');
            $table->json('surveyPhotos');
            $table->string('quotationReference');
            $table->string('quotationImage');
            $table->string('jcReference');
            $table->string('jcImage');
            $table->string('dcReference');
            $table->string('dcImage');
            $table->enum('status', ['Pending', 'In Progress', 'On Hold', 'Completed', 'Cancelled'])->default('Pending');
            $table->foreignId('assigned_by_admin')->constrained('admin')->onDelete('cascade');
            $table->foreignId('assigned_by_head')->nullable()->constrained('head')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->date('dueDate')->nullable();
            $table->timestamps();

        });
        Schema::create('project_assigned_to_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_assigned_to_users');
        Schema::dropIfExists('projects');
    }
};
