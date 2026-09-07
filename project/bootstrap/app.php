<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            // antes do HandleInertiaRequests: não vale a pena preparar os
            // dados de uma página para quem vai ser reencaminhado para o login
            EnsureUserIsActive::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Em local e testing queremos a exceção real: os testes dependem disso
        // para apanhar regressões, e o debug fica impossível sem o stack trace.
        // Fora daí, estes códigos passam a ser uma página Inertia própria em
        // vez da página de erro do Laravel (em inglês) ou, pior, da janela
        // sobreposta que o Inertia mostra por cima da navegação do painel.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (app()->environment(['local', 'testing']) || $request->is('api/*')) {
                return $response;
            }

            if (in_array($response->getStatusCode(), [403, 404, 419, 500, 503], true)) {
                return Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
