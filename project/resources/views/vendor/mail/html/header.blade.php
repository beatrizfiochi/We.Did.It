@props(['url'])
{{--
    Cabeçalho com a marca do CESAE Digital (SCRUM-137).

    O template original do Laravel troca o texto por um logo alojado em
    laravel.com sempre que o nome da aplicação é "Laravel". Aqui o logo é
    sempre o nosso, servido pelo próprio site.

    Não usa a classe .logo do tema: essa é 75x75 quadrada, feita para o
    logo do Laravel, e esmagaria uma marca em formato largo.

    O alt vem do slot (config('app.name')) de propósito. Muitos clientes de
    email bloqueiam imagens remotas por defeito — para uma boa parte dos
    destinatários, este texto é o cabeçalho.
--}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/logo-email.png') }}"
     width="165"
     alt="{{ trim($slot) }}"
     style="width: 165px; max-width: 165px; height: auto; border: 0; display: block; margin: 0 auto;">
</a>
</td>
</tr>
