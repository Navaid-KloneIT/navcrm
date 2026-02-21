<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            $table->string('survey_number', 20);
            $table->string('type', 10);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 10)->default('draft');
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('token', 64)->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'survey_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
