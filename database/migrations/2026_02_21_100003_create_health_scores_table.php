<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('overall_score');
            $table->unsignedTinyInteger('login_score');
            $table->unsignedTinyInteger('ticket_score');
            $table->unsignedTinyInteger('payment_score');
            $table->json('factors')->nullable();
            $table->timestamp('calculated_at');

            $table->timestamps();

            $table->index(['tenant_id', 'account_id']);
            $table->index(['tenant_id', 'overall_score']);
            $table->index('calculated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_scores');
    }
};
