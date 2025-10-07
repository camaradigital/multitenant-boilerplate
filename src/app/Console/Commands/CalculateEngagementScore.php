<?php

namespace App\Console\Commands\Tenant;

use App\Models\Tenant\User;
use Illuminate\Console\Command;

class CalculateEngagementScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:calculate-engagement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates and updates the engagement score for all citizens.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting engagement score calculation for all citizens...');

        $cidadaosQuery = User::role('Cidadao');
        $totalCidadaos = $cidadaosQuery->count();

        if ($totalCidadaos === 0) {
            $this->info('No citizens found to process.');
            return 0;
        }

        $bar = $this->output->createProgressBar($totalCidadaos);
        $bar->start();

        // Usamos chunkById para processar os usuários em lotes de forma eficiente
        $cidadaosQuery->chunkById(100, function ($cidadaos) use ($bar) {
            foreach ($cidadaos as $cidadao) {
                $score = 0;

                // +2 pontos por solicitação de serviço
                $score += $cidadao->solicitacoes()->count() * 2;

                // +5 pontos por mensagem ao gabinete (maior peso por indicar proatividade)
                $score += $cidadao->gabineteMessages()->count() * 5;

                // +3 pontos por pesquisa de satisfação respondida (valoriza o feedback)
                $score += $cidadao->pesquisas_satisfacao()->count() * 3;

                // +1 ponto por candidatura
                $score += $cidadao->candidaturas()->count() * 1;

                // +4 pontos por interação offline (nota interna)
                $score += $cidadao->notas()->count() * 4;

                // Atualiza o score do cidadão
                $cidadao->update(['engagement_score' => $score]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\nEngagement score calculation finished successfully for {$totalCidadaos} citizens.");

        return 0;
    }
}
