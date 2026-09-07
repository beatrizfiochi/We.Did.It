<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quais imagens de cada notícia/testemunho saem nesta edição da newsletter.
 *
 * Fica na pivot, não na notícia: a mesma notícia pode sair com imagens
 * diferentes em edições diferentes. Guarda os ids das linhas em `images`
 * (SCRUM-140), até 3, pela ordem em que a pessoa os escolheu.
 *
 * null  = ainda não foi feita escolha nesta edição — a pré-visualização
 *         cai na coluna espelho `image`, como antes.
 * []    = escolha feita e é "sem imagem nenhuma".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_newsletter', function (Blueprint $table) {
            $table->json('image_ids')->nullable()->after('order');
        });

        Schema::table('newsletter_testimonial', function (Blueprint $table) {
            $table->json('image_ids')->nullable()->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('news_newsletter', function (Blueprint $table) {
            $table->dropColumn('image_ids');
        });

        Schema::table('newsletter_testimonial', function (Blueprint $table) {
            $table->dropColumn('image_ids');
        });
    }
};
