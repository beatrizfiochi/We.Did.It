<x-mail::message>
# Nova submissão para moderar

Chegou {{ $type === 'Notícia' ? 'uma nova notícia' : 'um novo testemunho' }} pelo formulário público do site.

<x-mail::panel>
**{{ $title }}**

@if ($authorName)
Submetido por: {{ $authorName }}
@endif
Categoria: {{ $categoryName ?? 'Nenhuma' }}
Recebido em: {{ now()->format('d/m/Y \à\s H:i') }}
</x-mail::panel>

<x-mail::button :url="$type === 'Notícia' ? route('admin.news.index') : route('admin.testimonials.index')">
Abrir a moderação
</x-mail::button>

O conteúdo submetido não vai neste email de propósito: ainda não foi moderado.
Entra no painel para o ler e decidir se é aprovado.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
