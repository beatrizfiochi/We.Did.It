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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->dateTime('datatime');
            $table->string('table'); // nome da tabela afetada (ex: "testimonial", "new")
            $table->unsignedBigInteger('pseudo_foreign_key')->nullable(); // id do registo afetado (ex: id da noticia)
            $table->enum('operation', ['created', 'updated', 'removed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
