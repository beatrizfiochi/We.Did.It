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
        Schema::create('newsletter_testimonial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_id')->constrained('newsletters')->onDelete('cascade');
            $table->foreignId('testimonial_id')->constrained('testimonials')->onDelete('cascade');
            $table->integer('order')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_testimonial');
    }
};
