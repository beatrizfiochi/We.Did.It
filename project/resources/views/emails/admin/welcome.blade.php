<x-mail::message>
# Olá, {{ $name }}

{{ $createdBy }} criou-te uma conta de acesso no {{ config('app.name') }}.

A partir de agora podes moderar as notícias e os testemunhos que chegam pelo
formulário público, gerir as ofertas formativas e a agenda, e montar as edições
da newsletter.

<x-mail::button :url="route('login')">
Entrar no painel
</x-mail::button>

A palavra-passe não vai neste email: foi definida por quem criou a conta, e é a
essa pessoa que a deves pedir. Se a perderes, usa o **Recuperar palavra-passe** no
ecrã de entrada.

Até já,<br>
{{ config('app.name') }}
</x-mail::message>
