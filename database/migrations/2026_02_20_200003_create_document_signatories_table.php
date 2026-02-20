<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signatories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('email');
            $table->string('sign_token')->unique();

            $table->string('status')->default('pending'); // pending, viewed, signed, rejected

            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->longText('signature_data')->nullable(); // base64 PNG from canvas
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('document_id');
            $table->index('sign_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatories');
    }
};
