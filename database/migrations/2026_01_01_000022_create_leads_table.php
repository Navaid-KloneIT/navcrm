<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('new');
            $table->string('score')->default('cold');
            $table->string('source')->nullable();
            $table->boolean('is_converted')->default(false);
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('converted_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'score']);
            $table->index(['tenant_id', 'is_converted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
