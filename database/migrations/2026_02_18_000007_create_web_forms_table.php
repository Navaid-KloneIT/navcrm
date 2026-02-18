<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('fields')->nullable();
            $table->string('submit_button_text')->default('Submit');
            $table->text('success_message')->nullable();
            $table->string('redirect_url')->nullable();
            $table->foreignId('assign_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('assign_by_geography')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('total_submissions')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_forms');
    }
};
