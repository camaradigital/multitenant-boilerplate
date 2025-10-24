<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\StatusSolicitacao; // Certifique-se que o namespace do seu Model está correto

class StatusSolicitacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Status Inicial (Padrão de Abertura)
        // Usamos updateOrCreate para garantir que APENAS UM status tenha is_default_abertura = true.
        // Primeiro, garantimos que nenhum outro status tenha essa flag.
        StatusSolicitacao::where('is_default_abertura', true)->update(['is_default_abertura' => false]);

        // Agora, criamos ou atualizamos o status que DEVE ser o padrão de abertura.
        StatusSolicitacao::updateOrCreate(
            ['nome' => 'Aguardando Atendimento'], // Critério para encontrar ou criar
            [ // Dados para inserir ou atualizar
                'cor' => '#3498db', // Azul, por exemplo
                'is_default_abertura' => true,
                'is_final' => false, // Não é um status final
                // 'ordem' => 1, // Opcional: para ordenar os status na interface
            ]
        );

        // 2. Status "Em Andamento" (Essencial para a lógica do controller)
        StatusSolicitacao::updateOrCreate(
            ['nome' => 'Andamento'],
            [
                'cor' => '#f1c40f', // Amarelo/Laranja, por exemplo
                'is_default_abertura' => false,
                'is_final' => false,
                // 'ordem' => 2,
            ]
        );

        // 3. Status "Concluído" (Status Final Comum)
        StatusSolicitacao::updateOrCreate(
            ['nome' => 'Concluído'],
            [
                'cor' => '#2ecc71', // Verde, por exemplo
                'is_default_abertura' => false,
                'is_final' => true, // Marca como um status de finalização
                // 'ordem' => 10, // Ordem maior para aparecer por último, talvez
            ]
        );

        // 4. Status "Cancelado" (Outro Status Final Comum)
        StatusSolicitacao::updateOrCreate(
            ['nome' => 'Cancelado'],
            [
                'cor' => '#e74c3c', // Vermelho, por exemplo
                'is_default_abertura' => false,
                'is_final' => true, // Também marca como finalização
                // 'ordem' => 11,
            ]
        );


        StatusSolicitacao::updateOrCreate(
            ['nome' => 'Aguardando Documentação'],
            [
                'cor' => '#9b59b6', // Roxo, por exemplo
                'is_default_abertura' => false,
                'is_final' => false,
                // 'ordem' => 3,
            ]
        );
    }
}
