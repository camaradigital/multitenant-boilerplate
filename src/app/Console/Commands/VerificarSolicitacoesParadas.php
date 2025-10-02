<?php

namespace App\Console\Commands;

use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\User;
use App\Notifications\Tenant\SolicitacaoParada;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class VerificarSolicitacoesParadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verificar-solicitacoes-paradas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica solicitações de serviço paradas (não atribuídas ou sem atualização) por mais de 48 horas e notifica os administradores.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando solicitações paradas para o tenant atual...');

        // Obter IDs de status que não são finais (ex: Andamento)
        $nonFinalStatusIds = StatusSolicitacao::where('is_final', false)->pluck('id');

        if ($nonFinalStatusIds->isEmpty()) {
            $this->warn('Nenhum status não-final encontrado. Impossível verificar solicitações paradas.');

            return;
        }

        // Critério 1: PARADAS POR FALTA DE ATRIBUIÇÃO (sem atendente e criada há > 48h)
        $unassignedStalled = SolicitacaoServico::whereNull('atendente_id')
            ->whereIn('status_id', $nonFinalStatusIds)
            ->where('created_at', '<=', now()->subHours(48))
            ->get();

        // Critério 2: PARADAS POR FALTA DE ATUALIZAÇÃO (com atendente e sem atualização há > 48h)
        $assignedStalled = SolicitacaoServico::whereNotNull('atendente_id')
            ->whereIn('status_id', $nonFinalStatusIds)
            ->where('updated_at', '<=', now()->subHours(48))
            ->get();

        $totalStalledCount = $unassignedStalled->count() + $assignedStalled->count();

        if ($totalStalledCount === 0) {
            $this->line('Nenhuma solicitação parada encontrada.');

            return;
        }

        $this->info("Encontradas {$totalStalledCount} solicitações paradas. A notificar administradores...");

        // Obter administradores para notificar
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'Admin Tenant')->where('guard_name', 'tenant');
        })->get();

        if ($admins->isEmpty()) {
            $this->warn('Nenhum Admin Tenant encontrado para notificar.');

            return;
        }

        // Notificar sobre solicitações não atribuídas
        foreach ($unassignedStalled as $solicitacao) {
            $reason = 'não foi atribuída a nenhum atendente há mais de 48 horas.';
            Notification::send($admins, new SolicitacaoParada($solicitacao, $reason));
        }

        // Notificar sobre solicitações sem atualização
        foreach ($assignedStalled as $solicitacao) {
            $reason = 'está sem nenhuma atualização há mais de 48 horas.';
            Notification::send($admins, new SolicitacaoParada($solicitacao, $reason));
        }

        $this->info('Notificações enviadas.');

        return 0;
    }
}
