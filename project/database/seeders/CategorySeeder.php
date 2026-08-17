<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            'Abertura de Turmas',
            'Entrega de diplomas',
            'Novo/a colaborador',
            'Divulgação',
            'Visita de estudo',
            'Parcerias',
            'Estágios',
            'Competições',
            'Projetos',
            'Seminários/Conferências',
            'Evento interno',
            'Evento externo',
            'Testemunho interno',
            'Testemunho externo',
        ])->each(fn (string $name) => Category::create(['name' => $name]));
    }
}
