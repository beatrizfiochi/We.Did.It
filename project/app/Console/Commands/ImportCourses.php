<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\CesaeCourseScraper;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('courses:import')]
#[Description('Importa as ofertas formativas do site do CESAE Digital')]
class ImportCourses extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CesaeCourseScraper $scraper): int
    {
        $items = $scraper->fetch();

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
