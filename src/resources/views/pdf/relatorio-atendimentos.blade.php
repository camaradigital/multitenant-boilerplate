<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Atendimentos</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Atendimentos</h1>
        <p>Período de {{ $data_inicio }} até {{ $data_fim }}</p>
        <p>Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Protocolo</th>
                <th>Cidadão</th>
                <th>Serviço</th>
                <th>Status</th>
                <th>Atendente</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($solicitacoes as $solicitacao)
                <tr>
                    <td>#{{ $solicitacao->id }}</td>
                    <td>{{ $solicitacao->cidadao->name ?? 'N/A' }}</td>
                    <td>{{ $solicitacao->servico->nome ?? 'N/A' }}</td>
                    <td>{{ $solicitacao->status->nome ?? $solicitacao->status }}</td>
                    <td>{{ $solicitacao->atendente->name ?? 'Não atribuído' }}</td>
                    <td>{{ $solicitacao->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum atendimento encontrado para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $tenant_name }} - Página {{ '{PAGE_NUM}' }} de {{ '{PAGE_COUNT}' }}
    </div>
</body>
</html>
