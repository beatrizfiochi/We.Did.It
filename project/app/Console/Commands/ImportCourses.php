<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\CesaeCourseScraper;
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
     * que interessa saber é quem mexeu no quê pelo admin (SCRUM-105).
     */
    public function handle(CesaeCourseScraper $scraper): int
    {
        try {
            $items = $scraper->fetch();
        } catch (Throwable $e) {
            $this->error('Falha ao importar: '.$e->getMessage());

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;

        foreach ($items as $item) {
            $url = $item['url'];
            unset($item['url']);

            $course = Course::where('url', $url)->first();

            if ($course) {
                // status não entra aqui: uma reimportação não deve repor a
                // received um curso que o gestor já aprovou ou recusou.
                $course->update($item);
                $updated++;
            } else {
                Course::create([...$item, 'url' => $url, 'status' => 'received']);
                $created++;
            }
        }

        $this->info(sprintf(
            '%d cursos encontrados, %d criados, %d atualizados.',
            count($items),
            $created,
            $updated,
        ));

        return self::SUCCESS;
    }
}
