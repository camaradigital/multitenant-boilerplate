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
        <p>Período de <?php echo e($data_inicio); ?> até <?php echo e($data_fim); ?></p>
        <p>Gerado em: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
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
            <?php $__empty_1 = true; $__currentLoopData = $solicitacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solicitacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>#<?php echo e($solicitacao->id); ?></td>
                    <td><?php echo e($solicitacao->cidadao->name ?? 'N/A'); ?></td>
                    <td><?php echo e($solicitacao->servico->nome ?? 'N/A'); ?></td>
                    <td><?php echo e($solicitacao->status->nome ?? $solicitacao->status); ?></td>
                    <td><?php echo e($solicitacao->atendente->name ?? 'Não atribuído'); ?></td>
                    <td><?php echo e($solicitacao->created_at->format('d/m/Y')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum atendimento encontrado para os filtros selecionados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <?php echo e($tenant_name); ?> - Página <?php echo e('{PAGE_NUM}'); ?> de <?php echo e('{PAGE_COUNT}'); ?>

    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/pdf/relatorio-atendimentos.blade.php ENDPATH**/ ?>