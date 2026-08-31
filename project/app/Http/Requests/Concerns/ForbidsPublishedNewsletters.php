<?php

namespace App\Http\Requests\Concerns;

/**
 * Recusa qualquer escrita a uma newsletter já publicada (SCRUM-116).
 *
 * Vive no authorize() e não no controller porque o authorize() corre antes da
 * validação: no controller, o pedido morria primeiro nas regras e devolvia um
 * erro de formulário em vez do 403 que a situação é.
 *
 * A regra em si está no Newsletter::isEditable() — aqui é só onde se aplica.
 * O trait existe porque estas linhas eram idênticas nos cinco FormRequests de
 * escrita da newsletter.
 */
trait ForbidsPublishedNewsletters
{
    public function authorize(): bool
    {
        return $this->route('newsletter')->isEditable();
    }
}
