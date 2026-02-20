<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');

            $table->date('date');
            $table->decimal('hours', 8, 2);
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->decimal('billable_rate', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'project_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
