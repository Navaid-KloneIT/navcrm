<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('trigger_entity_type');
            $table->unsignedBigInteger('trigger_entity_id');
            $table->string('status')->default('pending');
            $table->json('context_data')->nullable();
            $table->json('actions_log')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('triggered_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['workflow_id', 'status']);
            $table->index(['tenant_id', 'triggered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_runs');
    }
};
