<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendar_newsletter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained('calendars')->onDelete('cascade');
            $table->foreignId('newsletter_id')->constrained('newsletters')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_newsletter');
    }
};
