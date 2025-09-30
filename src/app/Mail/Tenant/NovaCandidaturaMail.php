<?php

namespace App\Mail\Tenant;

use App\Models\Tenant\Candidatura;
use App\Models\Tenant\User;
use App\Models\Tenant\Vaga;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NovaCandidaturaMail extends Mailable
{
    use Queueable, SerializesModels;

    // Declaração explícita das propriedades públicas para garantir que a view as aceda
    public Vaga $vaga;
    public User $candidato;
    public Candidatura $candidatura;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Vaga $vaga, User $candidato, Candidatura $candidatura)
    {
        // Atribuição manual no construtor
        $this->vaga = $vaga;
        $this->candidato = $candidato;
        $this->candidatura = $candidatura;
    }

    /**
     * Build the message.
     * Este método garante que o template Blade seja processado corretamente.
     *
     * @return $this
     */
    public function build()
    {
        $nomeArquivo = 'curriculo_' . Str::slug($this->candidato->name) . '.' . pathinfo($this->candidatura->curriculo_path, PATHINFO_EXTENSION);

        $curriculoContent = Storage::disk('local')->get($this->candidatura->curriculo_path);

        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Nova Candidatura Recebida para a Vaga: ' . $this->vaga->titulo)
            ->view('emails.tenant.nova_candidatura')
            // Anexamos os dados do ficheiro diretamente, em vez de um caminho.
            ->attachData($curriculoContent, $nomeArquivo, [
                'mime' => 'application/octet-stream',
            ]);
    }
}

