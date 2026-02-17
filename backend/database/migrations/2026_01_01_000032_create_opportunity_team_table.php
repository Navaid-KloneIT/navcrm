<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('owner');
            $table->decimal('split_percentage', 5, 2)->default(100);
            $table->timestamps();

            $table->unique(['opportunity_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_team');
    }
};
