<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['account_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_contact');
    }
};
