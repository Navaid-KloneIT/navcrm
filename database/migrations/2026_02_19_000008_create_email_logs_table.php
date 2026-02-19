<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();

            $table->string('direction');        // inbound, outbound
            $table->string('source');           // gmail, outlook, bcc_dropbox, manual
            $table->string('subject');
            $table->longText('body')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->json('cc')->nullable();     // array of CC addresses
            $table->string('message_id')->nullable()->index(); // external deduplication ID

            // Tracking
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();

            // Polymorphic association (Contact, Lead, Account)
            $table->nullableMorphs('emailable');

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'direction']);
            $table->index(['tenant_id', 'sent_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
