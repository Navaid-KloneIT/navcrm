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
            $table->unsignedBigInteger('tenant_id')->index();

            $table->string('direction');        // inbound, outbound
            $table->string('status');           // completed, no_answer, busy, voicemail, failed
            $table->string('phone_number')->nullable();
            $table->unsignedInteger('duration')->nullable(); // seconds
            $table->string('recording_url')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('called_at');

            // Polymorphic association (Contact, Lead, Account)
            $table->nullableMorphs('loggable');

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'direction']);
            $table->index(['tenant_id', 'called_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
