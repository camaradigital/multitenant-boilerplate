<?php

namespace App\Exports\Tenant;

use App\Services\Tenant\RelatorioService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CidadaosExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return app(RelatorioService::class)->gerarRelatorioCidadaosQuery($this->filters);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome Completo',
            'CPF',
            'E-mail',
            'Data de Cadastro',
            'Total de Solicitações',
            'Status',
        ];
    }

    public function map($cidadao): array
    {
        return [
            $cidadao->id,
            $cidadao->name,
            $cidadao->cpf,
            $cidadao->email,
            $cidadao->created_at->format('d/m/Y H:i'),
            $cidadao->solicitacoes_count,
            $cidadao->is_active ? 'Ativo' : 'Inativo',
        ];
    }
}

