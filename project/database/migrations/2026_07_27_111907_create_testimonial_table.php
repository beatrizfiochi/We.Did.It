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
        Schema::create('testimonial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_category');
            $table->foreign('id_category')->references('id')->on('category')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('title');
            $table->text('description');
            $table->string('image'); // guarda o caminho/nome do ficheiro, não o ficheiro em si
            $table->dateTime('datatime');
            $table->enum('status', ['accepted', 'refused'])->default('accepted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonial');
    }
};
