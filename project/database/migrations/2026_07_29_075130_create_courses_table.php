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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('imageUrl')->nullable();
            $table->string('location')->nullable();
            $table->string('schedule')->nullable();
            $table->string('start_date');
            $table->string('price');
            $table->string('status');
            $table->string('url');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
