<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');

            $table->string('name');
            $table->string('type'); // DocumentType enum value
            $table->text('description')->nullable();
            $table->longText('body'); // HTML with {{Variable}} placeholders
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
