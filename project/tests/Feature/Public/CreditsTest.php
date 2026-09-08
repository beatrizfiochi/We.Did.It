<?php

namespace Tests\Feature\Public;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * A página de créditos da equipa.
 *
 * É pública e fica fora da navegação: chega-se lá pelo link do rodapé. O teste
 * existe porque uma página sem entrada no menu é a primeira a partir sem
 * ninguém dar por isso.
 */
class CreditsTest extends TestCase
{
    public function test_the_credits_page_is_public(): void
    {
        $this->get(route('creditos'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Creditos'));
    }
}
