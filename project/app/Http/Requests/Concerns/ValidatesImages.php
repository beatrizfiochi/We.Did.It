<?php

namespace App\Http\Requests\Concerns;

use App\Models\Image;

/**
 * As regras e mensagens de imagem partilhadas pelos quatro pedidos que aceitam
 * ficheiros: as duas submissões públicas e as duas edições de moderação
 * (SCRUM-140).
 *
 * Existe por causa de uma divergência real: as mensagens por ficheiro foram
 * traduzidas nos StoreRequest e ficaram por traduzir nos UpdateRequest, e um
 * moderador que anexasse um PDF via a mensagem em inglês do Laravel. Com quatro
 * cópias das mesmas linhas, a pergunta não era se voltavam a divergir, era
 * quando.
 *
 * Fica de fora a regra do próprio campo 'images': o limite é fixo nas
 * submissões e calculado nas edições, onde as imagens já gravadas contam.
 */
trait ValidatesImages
{
    /**
     * @return array<string, array<int, string>>
     */
    protected function imageRules(): array
    {
        return [
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:'.Image::MAX_KB_POR_FICHEIRO],

            // O campo era 'image' até à SCRUM-140. Sem esta regra, um cliente
            // desatualizado — um build antigo em cache, por exemplo — enviava
            // o ficheiro no nome velho e a submissão era aceite sem a imagem,
            // em silêncio. Prohibited transforma a perda calada num erro visível.
            'image' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function imageMessages(): array
    {
        return [
            'images.*.image' => 'Cada ficheiro tem de ser uma imagem.',
            'images.*.mimes' => 'As imagens têm de ser jpg, jpeg ou png.',
            'images.*.max' => 'Cada imagem deve ter no máximo '.(Image::MAX_KB_POR_FICHEIRO / 1024).'MB.',
            'image.prohibited' => 'A página está desatualizada. Recarrega e tenta outra vez.',
        ];
    }
}
