<?php

namespace App\Services;

use App\Models\Course;

/**
 * Grava os cursos que o CesaeCourseScraper devolve. Partilhado entre o
 * comando artisan (courses:import) e o botão do admin — a regra de não
 * repor o status de um curso já moderado só pode existir num sítio.
 */
class CourseImporter
{
    public function __construct(private CesaeCourseScraper $scraper) {}

    /**
     * @return array{total: int, created: int, updated: int}
     */
    public function import(): array
    {
        $items = $this->scraper->fetch();

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

        return [
            'total' => count($items),
            'created' => $created,
            'updated' => $updated,
        ];
    }
}
