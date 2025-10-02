<?php

namespace App\Jobs\Tenant;

use App\Mail\Tenant\CampanhaMail;
use App\Models\Tenant\CampanhaComunicacao;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCampanhaEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campanha;

    /**
     * Create a new job instance.
     */
    public function __construct(CampanhaComunicacao $campanha)
    {
        $this->campanha = $campanha;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $segmentacao = $this->campanha->segmentacao;
        $query = User::query()->where('is_active', true)->role('Cidadao');

        if (! empty($segmentacao['idade_min'])) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, JSON_UNQUOTE(JSON_EXTRACT(profile_data, "$.data_nascimento")), CURDATE()) >= ?', [$segmentacao['idade_min']]);
        }
        if (! empty($segmentacao['idade_max'])) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, JSON_UNQUOTE(JSON_EXTRACT(profile_data, "$.data_nascimento")), CURDATE()) <= ?', [$segmentacao['idade_max']]);
        }
        if (! empty($segmentacao['genero'])) {
            $query->whereJsonContains('profile_data->genero', $segmentacao['genero']);
        }
        if (! empty($segmentacao['renda_min'])) {
            $query->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(profile_data, "$.renda_familiar")) AS DECIMAL(10,2)) >= ?', [(float) $segmentacao['renda_min']]);
        }
        if (! empty($segmentacao['renda_max'])) {
            $query->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(profile_data, "$.renda_familiar")) AS DECIMAL(10,2)) <= ?', [(float) $segmentacao['renda_max']]);
        }
        if (! empty($segmentacao['bairros']) && is_array($segmentacao['bairros'])) {
            $query->whereIn('bairro', $segmentacao['bairros']);
        }

        $count = 0;
        $query->chunk(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                Mail::to($user->email)->send(new CampanhaMail($this->campanha->mensagem, $this->campanha->titulo));
                $count++;
            }
        });

        $this->campanha->update(['total_enviado' => $count]);
    }
}
