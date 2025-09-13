<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Cidadãos</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 11px; }
        .header { text-align: center; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 5px 0; font-size: 12px; color: #666; }
        .citizen-block { border: 1px solid #ccc; border-radius: 5px; padding: 15px; margin-bottom: 20px; page-break-inside: avoid; }
        .citizen-details { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .citizen-details h2 { margin: 0 0 10px 0; font-size: 16px; color: #000; }
        .detail-item { display: inline-block; margin-right: 20px; font-size: 11px; }
        .detail-item strong { color: #555; }
        .solicitacoes-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 10px; }
        .solicitacoes-table th, .solicitacoes-table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .solicitacoes-table th { background-color: #f2f2f2; font-weight: bold; }
        .solicitacoes-table tr:nth-child(even) { background-color: #f9f9f9; }
        .no-solicitacoes { font-style: italic; color: #888; }
        .footer { position: fixed; bottom: -10mm; left: 0; right: 0; text-align: center; font-size: 9px; color: #aaa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Cidadãos</h1>
        <p>Relatório detalhado de cidadãos e suas solicitações</p>
        <p>Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @forelse ($cidadaos as $cidadao)
        <div class="citizen-block">
            <div class="citizen-details">
                <h2>{{ $cidadao->name }}</h2>
                <div class="detail-item"><strong>CPF:</strong> {{ $cidadao->cpf ?? 'N/A' }}</div>
                <div class="detail-item"><strong>E-mail:</strong> {{ $cidadao->email }}</div>
                <div class="detail-item"><strong>Cadastro:</strong> {{ $cidadao->created_at->format('d/m/Y') }}</div>
                <div class="detail-item"><strong>Status:</strong> {{ $cidadao->is_active ? 'Ativo' : 'Inativo' }}</div>
            </div>

            <h3>Histórico de Solicitações ({{ $cidadao->solicitacoes->count() }})</h3>
            @if($cidadao->solicitacoes->isNotEmpty())
                <table class="solicitacoes-table">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Serviço</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Atendente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cidadao->solicitacoes->sortByDesc('created_at') as $solicitacao)
                            <tr>
                                <td>#{{ $solicitacao->id }}</td>
                                <td>{{ $solicitacao->servico->nome ?? 'N/A' }}</td>
                                <td>{{ $solicitacao->status->nome ?? $solicitacao->status }}</td>
                                <td>{{ $solicitacao->created_at->format('d/m/Y') }}</td>
                                <td>{{ $solicitacao->atendente->name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-solicitacoes">Nenhuma solicitação registrada para este cidadão.</p>
            @endif
        </div>
    @empty
        <div class="citizen-block" style="text-align: center;">
            <p>Nenhum cidadão encontrado para os filtros selecionados.</p>
        </div>
    @endforelse

    <div class="footer">
        {{ $tenant_name }} - Página <span class="pagenum"></span>
    </div>
</body>
</html>
