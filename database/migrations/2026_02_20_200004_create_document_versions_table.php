<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedSmallInteger('version_number');
            $table->longText('body'); // HTML snapshot before edit

            $table->timestamp('created_at')->nullable(); // no updated_at

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
