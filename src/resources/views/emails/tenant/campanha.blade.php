@php
// Define o nome do tenant de forma segura no início do arquivo.
// Adiciona um valor padrão 'Nossa Equipe' caso o nome não seja encontrado.
$tenantName = \Spatie\Multitenancy\Models\Tenant::current()?->name ?? 'Nossa Equipe';
@endphp

<x-mail::message>
{{-- Título principal do e-mail --}}
# {{ $titulo }}

{{-- Saudação inicial para o destinatário --}}
Olá!

{{-- Linha de introdução que contextualiza a mensagem --}}
Você está recebendo uma nova comunicação da nossa equipe. Por favor, leia a mensagem abaixo:

{{-- Painel de destaque para a mensagem principal (usando a sintaxe de citação do Markdown) --}}
> {{ $mensagem }}

{{-- Botão de "Chamada para Ação" (Call to Action) como um link Markdown --}}
[Acessar o Portal]({{ url('/') }})

{{-- Linha de conclusão do e-mail --}}
Agradecemos a sua atenção e estamos à disposição para qualquer esclarecimento.

{{-- Saudação final formal --}}
Atenciosamente,
Equipe {{ $tenantName }}
</x-mail::message>
