<?php
namespace App\Services\Tenant;

use App\Models\Tenant\LogUsoServico;
use App\Models\Tenant\Servico;
use App\Models\Tenant\User; // <-- CORREÇÃO
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ServicoLimiterService
{
    public function podeSolicitar(Servico $servico, User $cidadao): void
    {
        if (empty($servico->regras_limite)) {
            return; // Se não há regras, pode solicitar.
        }

        $regras = $servico->regras_limite;
        $limite = $regras['limite'];
        $periodo = $regras['periodo'];
        $agora = Carbon::now();
        $inicioPeriodo = null;

        switch ($periodo) {
            case 'diario': $inicioPeriodo = $agora->startOfDay(); break;
            case 'semanal': $inicioPeriodo = $agora->startOfWeek(); break;
            case 'mensal': $inicioPeriodo = $agora->startOfMonth(); break;
            case 'anual': $inicioPeriodo = $agora->startOfYear(); break;
        }

        if ($inicioPeriodo) {
            $usosNoPeriodo = LogUsoServico::where('user_id', $cidadao->id)
                ->where('servico_id', $servico->id)
                ->where('created_at', '>=', $inicioPeriodo)
                ->count();

            if ($usosNoPeriodo >= $limite) {
                throw ValidationException::withMessages([
                    'servico' => "Limite de {$limite} solicitações por {$periodo} atingido para este serviço."
                ]);
            }
        }
    }
}
