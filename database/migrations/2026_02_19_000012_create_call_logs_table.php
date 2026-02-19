<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('direction', 10)->default('outbound'); // inbound, outbound
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('status', 20)->default('completed'); // completed, no_answer, busy, voicemail, failed
            $table->string('phone_number', 30)->nullable();
            $table->string('recording_url')->nullable();
            $table->nullableMorphs('loggable'); // loggable_type, loggable_id
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('called_at');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('called_at');
            $table->index('direction');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
