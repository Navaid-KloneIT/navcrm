<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('web_form_id')->constrained()->cascadeOnDelete();
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_converted')->default(false);
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'web_form_id']);
            $table->index(['tenant_id', 'is_converted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_form_submissions');
    }
};
