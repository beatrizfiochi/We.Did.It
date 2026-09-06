{{-- blade-formatter-disable --}}                       
<x-mail::message>
# Olá!

Recebeste este email porque foi pedida a recuperação da palavra-passe da tua conta.

<x-mail::button :url="$url">
Definir nova palavra-passe
</x-mail::button>

Esta ligação expira dentro de **{{ $count }}** minutos.

Se não foste tu a pedir, não precisas de fazer nada.

Até já,<br>
{{ config('app.name') }}

<hr style="border: none; border-top: 1px solid #e2e8f0; margin: 25px 0 15px 0;">

<p style="font-size: 14px; color: #718096; line-height: 1.5; word-break: break-all;">
Se estiveres a ter problemas em clicar no botão "Definir nova palavra-passe", copia e cola o URL abaixo no teu navegador:<br>
<a href="{{ $url }}" style="color: #3182ce; text-decoration: underline;">{{ $url }}</a>
</p>

</x-mail::message>