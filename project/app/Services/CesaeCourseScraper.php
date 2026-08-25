<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * Vai buscar e interpreta a lista de cursos de
 * https://www.cesaedigital.pt/fldrSite/pages/coursesList.aspx.
 *
 * Não é uma API JSON — é scraping de HTML. A tradução dos seletores segue
 * EcraCESAEEntrada-main/src/sources/courses.js, com uma correção: o site
 * original lê `.label` em todo o cartão, o que mistura o selo de preço
 * ("Valor por semana") com o local/horário quando ambos existem. Aqui o
 * `.label` fica restrito ao `.text-holder`, que é onde o local/horário vive.
 */
class CesaeCourseScraper
{
    private const URL = 'https://www.cesaedigital.pt/fldrSite/pages/coursesList.aspx';

    private const BASE = 'https://www.cesaedigital.pt';

    private const MONTHS = [
        'janeiro' => 1, 'fevereiro' => 2, 'marco' => 3, 'abril' => 4,
        'maio' => 5, 'junho' => 6, 'julho' => 7, 'agosto' => 8,
        'setembro' => 9, 'outubro' => 10, 'novembro' => 11, 'dezembro' => 12,
    ];

    /**
     * Vai buscar o HTML ao site e devolve os cursos já interpretados.
     *
     * @return array<int, array<string, string|null>>
     */
    public function fetch(): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'CesaeKioskBot/1.0',
            'Accept-Language' => 'pt-PT,pt;q=0.9',
        ])->timeout(8)->get(self::URL);

        $response->throw();

        return $this->parse($response->body());
    }

    /**
     * Interpreta um HTML já obtido. Separado do fetch() para os testes não
     * dependerem da internet.
     *
     * @return array<int, array<string, string|null>>
     */
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);
        $items = [];

        $crawler->filter('article a[href^="/curso/lista/"]')->each(function (Crawler $link) use (&$items) {
            $title = $this->text($link, 'h1');

            if ($title === '') {
                return;
            }

            $href = $link->attr('href') ?? '';
            $url = str_starts_with($href, 'http') ? $href : self::BASE.$href;

            $imageNodes = $link->filter('img');
            $imageSrc = $imageNodes->count() > 0 ? $imageNodes->attr('src') : null;
            $imageUrl = $imageSrc
                ? (str_starts_with($imageSrc, 'http') ? $imageSrc : self::BASE.$imageSrc)
                : null;

            [$location, $schedule] = $this->parseLabel($this->text($link, '.text-holder .label'));

            $items[] = [
                'title' => $title,
                'url' => $url,
                'imageUrl' => $imageUrl,
                'location' => $location,
                'schedule' => $schedule,
                'start_date' => $this->normalizeStartDate($this->text($link, '.date')),
                'price' => $this->text($link, '.price'),
            ];
        });

        return $items;
    }

    private function text(Crawler $node, string $selector): string
    {
        $matches = $node->filter($selector);

        return $matches->count() > 0 ? trim($matches->text('')) : '';
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function parseLabel(string $label): array
    {
        if ($label === '') {
            return [null, null];
        }

        $parts = array_values(array_filter(
            array_map('trim', explode(',', $label)),
            fn (string $part) => $part !== '',
        ));

        if ($parts === []) {
            return [null, null];
        }

        if (count($parts) === 1) {
            return [$parts[0], null];
        }

        return [$parts[0], implode(', ', array_slice($parts, 1))];
    }

    /**
     * O site devolve a data por extenso em português ("29 de junho de 2026"),
     * não ISO. Sem isto, start_date_for_sorting nunca consegue interpretar as
     * datas importadas e todos os cursos vão parar ao fim da lista.
     *
     * O que não for reconhecível (ex.: "A anunciar") fica como veio — é
     * exatamente o caso que start_date_for_sorting já sabe tratar.
     */
    private function normalizeStartDate(string $raw): string
    {
        if ($raw === '') {
            return $raw;
        }

        if (preg_match('/^(\d{1,2})\s+de\s+(\p{L}+)\s+de\s+(\d{4})$/ui', $raw, $matches)) {
            $month = self::MONTHS[Str::ascii(mb_strtolower($matches[2]))] ?? null;

            if ($month !== null) {
                return sprintf('%04d-%02d-%02d', (int) $matches[3], $month, (int) $matches[1]);
            }
        }

        try {
            return Carbon::parse($raw)->toDateString();
        } catch (Throwable) {
            return $raw;
        }
    }
}
