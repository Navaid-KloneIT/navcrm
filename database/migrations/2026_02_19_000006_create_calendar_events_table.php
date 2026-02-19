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
            $table->unsignedBigInteger('tenant_id')->index();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type')->default('meeting'); // meeting, call, demo, follow_up, webinar, other
            $table->string('status')->default('scheduled');   // scheduled, completed, cancelled, no_show

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_all_day')->default(false);

            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('invite_url')->nullable(); // Calendly-style booking link

            // External calendar sync
            $table->string('external_calendar_id')->nullable();
            $table->string('external_calendar_source')->nullable(); // google, outlook, ical

            // Polymorphic association (Contact, Account, Opportunity)
            $table->nullableMorphs('eventable');

            $table->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'starts_at']);
            $table->index('organizer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
