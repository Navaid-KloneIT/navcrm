<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();

            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('pending');  // pending, in_progress, completed, cancelled

            // Recurrence
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_type')->nullable();     // daily, weekly, monthly, quarterly, yearly
            $table->unsignedTinyInteger('recurrence_interval')->default(1)->nullable();
            $table->date('recurrence_ends_at')->nullable();

            // Polymorphic association (Contact, Account, Opportunity)
            $table->nullableMorphs('taskable');

            // Ownership
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'due_date']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
