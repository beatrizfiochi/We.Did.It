<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Passa as imagens que já existem em news.image e testimonials.image para
     * linhas na tabela images, com order = 1 (SCRUM-140).
     *
     * A coluna antiga fica. Continua a espelhar a primeira imagem enquanto os
     * sete ecrãs que a leem não forem convertidos, e é o caminho de volta se
     * algo correr mal ao fim de semana. Sai depois da entrega.
     */
    public function up(): void
    {
        foreach ([
            'news' => 'App\Models\News',
            'testimonials' => 'App\Models\Testimonial',
        ] as $table => $type) {
            DB::table($table)
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($type) {
                    DB::table('images')->insert($rows->map(fn ($row) => [
                        'imageable_type' => $type,
                        'imageable_id' => $row->id,
                        'path' => $row->image,
                        'order' => 1,
                        // a imagem entrou quando a submissão entrou
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ])->all());
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('images')->whereIn('imageable_type', [
            'App\Models\News',
            'App\Models\Testimonial',
        ])->delete();
    }
};
