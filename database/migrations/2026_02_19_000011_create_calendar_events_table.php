<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('type', 30)->default('meeting'); // meeting, call, demo, follow_up, webinar, other
            $table->string('status', 20)->default('scheduled'); // scheduled, completed, cancelled, no_show
            $table->string('external_calendar_id')->nullable();
            $table->string('external_calendar_source', 20)->nullable(); // google, outlook, ical
            $table->string('invite_url')->nullable();
            $table->nullableMorphs('eventable'); // eventable_type, eventable_id
            $table->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('start_at');
            $table->index('status');
            $table->index('organizer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
