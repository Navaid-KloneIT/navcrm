<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('trigger_event');
            $table->json('trigger_config')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'trigger_event', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
