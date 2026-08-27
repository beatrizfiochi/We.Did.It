<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    /**
     * Em testing o guard mantém a exceção real (é o que os outros testes
     * precisam para apanhar regressões), por isso aqui só confirmamos o
     * status: sem ele, um URL inválido podia silenciosamente virar 200.
     */
    public function test_an_unknown_url_returns_404(): void
    {
        $this->get('/rota-que-nao-existe')->assertNotFound();
    }

    /**
     * Fora de local/testing é que a página Inertia própria entra em jogo —
     * é isso que impede o stack trace de aparecer numa demonstração ou em
     * produção.
     */
    public function test_a_404_outside_local_and_testing_renders_the_error_page_without_exposing_code(): void
    {
        $this->app['env'] = 'production';

        $response = $this->get('/rota-que-nao-existe');

        $response->assertNotFound();
        $response->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 404));
        $response->assertDontSee('Stack Trace', false);
        $response->assertDontSee('vendor/laravel', false);
    }

    /**
     * Confirma que os cinco códigos do ticket disparam mesmo a página Error,
     * cada um com o status certo — não só o 404, que os outros testes já
     * cobrem com uma rota real.
     */
    public function test_each_covered_status_code_renders_the_error_page(): void
    {
        $this->app['env'] = 'production';

        foreach ([403, 404, 419, 500, 503] as $status) {
            Route::get("/teste-erro-{$status}", fn () => abort($status));

            $response = $this->get("/teste-erro-{$status}");

            $response->assertStatus($status);
            $response->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', $status));
        }
    }
}
