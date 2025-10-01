<?php

namespace App\Events\Tenant;

use App\Models\Tenant\SolicitacaoServico;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceStatusUpdated
{
    use Dispatchable, SerializesModels;

    public SolicitacaoServico $solicitacao;

    public function __construct(SolicitacaoServico $solicitacao)
    {
        $this->solicitacao = $solicitacao;
    }
}
