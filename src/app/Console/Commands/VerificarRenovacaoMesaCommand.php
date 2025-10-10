<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LembreteRenovacaoMesaNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class VerificarRenovacaoMesaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verificar-renovacao-mesa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica se a Mesa Diretora da legislatura atual está próxima do período de renovação (2 anos) e notifica os administradores.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando verificação de renovação da Mesa Diretora para todos os tenants...');

        // Itera sobre cada tenant (cada câmara municipal)
        Tenant::all()->each(function ($tenant) {
            $tenant->execute(function () use ($tenant) {
                $this->line("Verificando tenant: {$tenant->name}");

                // 1. Encontra a legislatura atual para este tenant
                $legislaturaAtual = Legislatura::where('is_atual', true)->first();

                if (! $legislaturaAtual) {
                    $this->warn("Nenhuma legislatura atual encontrada para o tenant {$tenant->name}. Pulando.");

                    return;
                }

                // 2. Calcula a "idade" da legislatura em meses
                $dataInicio = Carbon::parse($legislaturaAtual->data_inicio);
                $agora = Carbon::now();
                $mesesDeMandato = $dataInicio->diffInMonths($agora);

                // 3. Define a janela para o lembrete (entre o 22º e o 24º mês do mandato)
                // Isso cobre o segundo semestre do segundo ano.
                if ($mesesDeMandato >= 22 && $mesesDeMandato <= 24) {
                    $this->info("Lembrete de renovação necessário para a legislatura '{$legislaturaAtual->titulo}'.");

                    // 4. Encontra os administradores do tenant para notificar
                    $administradores = User::whereHas('roles', function ($query) {
                        $query->where('name', 'Admin Tenant');
                    })->get();

                    if ($administradores->isEmpty()) {
                        $this->error("Nenhum administrador encontrado para notificar no tenant {$tenant->name}.");

                        return;
                    }

                    // 5. Envia a notificação
                    Notification::send($administradores, new LembreteRenovacaoMesaNotification($legislaturaAtual));

                    $this->info("Notificações enviadas para {$administradores->count()} administrador(es) do tenant {$tenant->name}.");
                } else {
                    $this->line("Legislatura '{$legislaturaAtual->titulo}' não está na janela de renovação ({$mesesDeMandato} meses). Nenhuma ação necessária.");
                }
            });
        });

        $this->info('Verificação concluída.');

        return 0;
    }
}
