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
        Schema::create('images', function (Blueprint $table) {
            $table->id();

            // Sem chave estrangeira, e não por esquecimento: uma relação
            // polimórfica aponta para tabelas diferentes conforme a linha, e o
            // MySQL não sabe exprimir isso. A integridade fica do lado do código.
            //
            // Hoje isso não custa nada, porque nada apaga submissões: as seis
            // rotas DELETE que existem são de agenda, categorias, cursos,
            // imagens, newsletters e perfil — notícias e testemunhos só mudam
            // de estado, com approve() e refuse().
            //
            // No dia em que aparecer um "eliminar notícia", as linhas daqui e
            // os ficheiros no disco ficam para trás. A saída é um deleting()
            // no model a percorrer images() e a reaproveitar o caminho do
            // ImageController::destroy — base de dados primeiro, ficheiro
            // depois, pela razão que está explicada lá.
            $table->morphs('imageable');
            $table->string('path');
            $table->unsignedTinyInteger('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
