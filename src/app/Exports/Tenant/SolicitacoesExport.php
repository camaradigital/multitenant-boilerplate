<?php

namespace App\Exports\Tenant;

use App\Models\Tenant\SolicitacaoServico;
use App\Services\Tenant\RelatorioService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SolicitacoesExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    /**
     * O construtor recebe os filtros que serão usados na consulta.
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Define a consulta que buscará os dados a serem exportados.
     * Reutilizamos o nosso RelatorioService para evitar duplicar código.
     */
    public function query()
    {
        return app(RelatorioService::class)->gerarRelatorioAtendimentosQuery($this->filters);
    }

    /**
     * Define os títulos das colunas na planilha.
     */
    public function headings(): array
    {
        return [
            'Protocolo',
            'Cidadão',
            'CPF do Cidadão',
            'Serviço Solicitado',
            'Tipo de Serviço',
            'Status',
            'Atendente Responsável',
            'Data da Solicitação',
            'Última Atualização',
        ];
    }

    /**
     * Mapeia cada linha de resultado para o formato desejado na planilha.
     *
     * @param  mixed  $solicitacao  O modelo Eloquent SolicitacaoServico.
     */
    public function map($solicitacao): array
    {
        return [
            $solicitacao->id,
            $solicitacao->cidadao->name ?? 'N/A', // Usamos '??' para evitar erros se não houver cidadão
            $solicitacao->cidadao->cpf ?? 'N/A',
            $solicitacao->servico->nome ?? 'N/A',
            $solicitacao->servico->tipoServico->nome ?? 'N/A',
            $solicitacao->status,
            $solicitacao->atendente->name ?? 'Não atribuído',
            $solicitacao->created_at->format('d/m/Y H:i'),
            $solicitacao->updated_at->format('d/m/Y H:i'),
        ];
    }
}
