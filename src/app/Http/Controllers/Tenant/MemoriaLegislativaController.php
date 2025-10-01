<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\Mandato; // <<< MUDANÇA AQUI: Importar o model Mandato
use Illuminate\Support\Facades\Storage; // <<< MUDANÇA AQUI: Importar o Storage
use Inertia\Inertia;

class MemoriaLegislativaController extends Controller
{
    public function show()
    {
        $legislaturas = Legislatura::with(['mandatos.politico'])
            ->orderBy('data_inicio', 'desc')
            ->get();

        return Inertia::render('Tenant/Portal/Publico/MemoriaLegislativa', [
            'timelineJson' => $this->formatDataForTimeline($legislaturas),
        ]);
    }

    private function formatDataForTimeline($legislaturas)
    {
        $events = [];

        foreach ($legislaturas as $leg) {
            $cargosOrder = ['Presidente', 'Vice-Presidente', '1º Secretário', '2º Secretário'];

            $mesaDiretora = $leg->mandatos
                ->whereIn('cargo', $cargosOrder)
                ->sortBy(fn ($mandato) => array_search($mandato->cargo, $cargosOrder));

            $demaisVereadores = $leg->mandatos->where('cargo', 'Vereador');

            // --- Construção do HTML do Slide ---
            $textBody = '<div class="timeline-slide-content">';
            if ($leg->texto_destaque) {
                $textBody .= '<p class="timeline-summary">'.nl2br(e($leg->texto_destaque)).'</p>';
            }

            // Galeria da Mesa Diretora
            if ($mesaDiretora->isNotEmpty()) {
                $textBody .= '<h4>Mesa Diretora</h4><div class="mesa-diretora-grid">';
                foreach ($mesaDiretora as $membro) {
                    // <<< MUDANÇA AQUI: Usando a nova função auxiliar
                    $textBody .= $this->buildPoliticoHtmlBlock($membro);
                }
                $textBody .= '</div>';
            }

            // Galeria dos Demais Vereadores
            if ($demaisVereadores->isNotEmpty()) {
                $textBody .= '<h4 class="mt-4">Demais Vereadores</h4><div class="gallery-grid">';
                foreach ($demaisVereadores as $vereador) {
                    // <<< MUDANÇA AQUI: Usando a nova função auxiliar
                    $textBody .= $this->buildPoliticoHtmlBlock($vereador);
                }
                $textBody .= '</div>';
            }
            $textBody .= '</div>';

            // Mídia principal
            $presidente = $mesaDiretora->firstWhere('cargo', 'Presidente');
            $mediaUrl = $leg->foto_principal_url ?: ($presidente?->politico->foto_url ?: '');

            $events[] = [
                'start_date' => ['year' => $leg->data_inicio->year, 'month' => $leg->data_inicio->month, 'day' => $leg->data_inicio->day],
                'end_date' => ['year' => $leg->data_fim->year, 'month' => $leg->data_fim->month, 'day' => $leg->data_fim->day],
                'text' => ['headline' => $leg->titulo, 'text' => $textBody],
                'media' => ['url' => $mediaUrl, 'caption' => 'Composição da '.$leg->titulo, 'thumbnail' => $mediaUrl],
                'group' => 'Legislaturas',
            ];
        }

        $primeiraLegislaturaComFoto = $legislaturas->firstWhere('foto_principal_url');
        $urlCapa = $primeiraLegislaturaComFoto ? $primeiraLegislaturaComFoto->foto_principal_url : '';

        return [
            'title' => [
                'media' => ['url' => $urlCapa, 'caption' => 'Câmara Municipal'],
                'text' => ['headline' => 'Memória Legislativa', 'text' => '<p>Navegue pela história política do nosso município.</p>'],
            ],
            'events' => $events,
        ];
    }

    /**
     * <<< MUDANÇA AQUI: NOVA FUNÇÃO AUXILIAR >>>
     * Constrói o bloco HTML para um político, incluindo o atributo data-councilor para o modal.
     */
    private function buildPoliticoHtmlBlock(Mandato $mandato): string
    {
        $politico = $mandato->politico;
        if (! $politico) {
            return '';
        }

        // 1. Gera a URL pública da foto, com um fallback se não existir.
        $fotoUrl = $politico->foto_path ? Storage::url($politico->foto_path) : asset('images/placeholder.png');

        // 2. Prepara os dados para o modal.
        $modalData = [
            'id' => $politico->id,
            'name' => $politico->nome_politico,
            'role' => $mandato->cargo,
            'photo_url' => $fotoUrl,
            'biography' => $politico->biografia ?? 'Biografia não disponível.',
        ];

        // 3. Codifica os dados como JSON e escapa para usar no atributo HTML de forma segura.
        $jsonData = htmlspecialchars(json_encode($modalData), ENT_QUOTES, 'UTF-8');

        // 4. Monta o HTML final com o atributo `data-councilor`.
        $html = "<div class='member-profile' data-councilor='{$jsonData}'>";
        $html .= "<img src='{$fotoUrl}' alt='Foto de {$politico->nome_politico}' />";
        $html .= "<span class='member-name'>{$politico->nome_politico}</span>";
        $html .= "<span class='member-role'>{$mandato->cargo}</span>";
        $html .= '</div>';

        return $html;
    }
}
