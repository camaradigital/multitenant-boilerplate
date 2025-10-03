<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampanhaComunicacaoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'mensagem' => $this->mensagem,
            'segmentacao' => $this->segmentacao, // O Laravel já deve decodificar o JSON para um array
            'total_enviado' => $this->total_enviado,
            'created_at' => $this->created_at->toIso8601String(), // Formato padrão para JavaScript

            // Inclui os dados do usuário que criou a campanha, se a relação foi carregada
            'createdBy' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ];
            }),
        ];
    }
}
