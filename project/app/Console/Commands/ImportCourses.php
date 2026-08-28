<?php

namespace App\Console\Commands;

use App\Services\CourseImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('courses:import')]
#[Description('Importa as ofertas formativas do site do CESAE Digital')]
class ImportCourses extends Command
{
    /**
     * Importa os cursos do site e grava-os, sem passar pelo CourseController.
     *
     * Não regista no ActivityLog de propósito: o record() usa auth()->id(), que é
     * null na consola. Registar aqui encheria a tabela de linhas sem autor a cada
     * importação, e o log passaria a ser sobre a máquina em vez das pessoas — o
     * que interessa saber é quem mexeu no quê pelo admin (SCRUM-105). O botão do
     * admin (CourseController::import) já regista, porque aí há sempre um autor.
     */
    public function handle(CourseImporter $importer): int
    {
        try {
            $result = $importer->import();
        } catch (Throwable $e) {
            $this->error('Falha ao importar: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            '%d cursos encontrados, %d criados, %d atualizados.',
            $result['total'],
            $result['created'],
            $result['updated'],
        ));

        return self::SUCCESS;
    }
}
