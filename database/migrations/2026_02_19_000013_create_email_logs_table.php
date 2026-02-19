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
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('direction', 10)->default('outbound'); // inbound, outbound
            $table->string('from_email');
            $table->string('to_email');
            $table->json('cc_emails')->nullable();
            $table->string('status', 20)->default('sent'); // sent, received, bounced, opened, clicked
            $table->string('source', 30)->default('manual'); // gmail, outlook, bcc_dropbox, manual
            $table->string('message_id')->nullable();
            $table->nullableMorphs('emailable'); // emailable_type, emailable_id
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('sent_at');
            $table->index('direction');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
