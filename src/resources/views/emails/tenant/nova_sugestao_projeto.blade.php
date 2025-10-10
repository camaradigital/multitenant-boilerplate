<x-mail::message>
# Nova Sugestão de Projeto de Lei Recebida

Uma **nova sugestão de projeto de lei** foi enviada através do Portal do Cidadão e está pronta para análise no sistema.

---

## 📄 Detalhes da Sugestão
| Item | Informação |
| :--- | :--- |
| **Protocolo** | **{{ $sugestao->protocolo }}** |
@if($sugestao->areaTematica)
| **Comissão (Área)** | {{ $sugestao->areaTematica->nome }} |
@endif

<x-mail::panel>
### Título
**{{ $sugestao->titulo }}**

### Descrição
{{ $sugestao->descricao }}
</x-mail::panel>

---

## 👤 Dados do Cidadão
* **Nome:** {{ $sugestao->cidadao_nome }}
* **E-mail:** {{ $sugestao->cidadao_email }}
* **Telefone:** {{ $sugestao->cidadao_telefone ?? 'Não informado' }}

---

<x-mail::button :url="$url" color="primary">
Analisar Sugestão no Sistema
</x-mail::button>

<x-mail::subcopy>
Esta é uma mensagem automática. Por favor, não responda a este e-mail.
</x-mail::subcopy>

Atenciosamente,

{{ config('app.name') }}
</x-mail::message>
