<x-mail::message>
{{-- CABEÇALHO COM A MARCA --}}
<x-slot:header>
    <x-mail::header :url="config('app.url')">
        {{-- Usando a cor da sua identidade visual --}}
        <h1 style="font-size: 24px; font-weight: bold; color: #059669; text-align: left;">
            {{ config('app.name') }}
        </h1>
    </x-mail::header>
</x-slot:header>

{{-- CONTEÚDO PRINCIPAL --}}

# Uma Novidade Para a Gestão da Sua Câmara

{{-- Corpo do E-mail (Vem da sua variável $body) --}}
{!! $body !!}

{{-- Botão de Chamada para Ação (CTA) --}}
<x-mail::button :url="$ctaUrl" color="success">
{{ $ctaText ?? 'Saiba Mais' }}
</x-mail::button>

{{-- PAINEL DE DESTAQUE PARA REFORÇAR A MENSAGEM --}}
<x-mail::panel>
    O <strong>Sistema CAC</strong> é a solução completa para modernizar o atendimento, garantir a conformidade com a LGPD e fortalecer o vínculo entre a sua gestão e os cidadãos.
</x-mail::panel>

Atenciosamente,<br>
Equipe do {{ config('app.name') }}

{{-- Subcopy para o link de descadastro --}}
<x-slot:subcopy>
Se você não deseja mais receber nossos comunicados, por favor, <a href="{{ $unsubscribeUrl }}">cancele sua inscrição aqui</a>.
</x-slot>
</x-mail::message>
