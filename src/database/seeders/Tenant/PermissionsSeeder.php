<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este seeder organiza as permissões de forma modular e granular, seguindo o padrão "modulo.acao".
     */
    public function run(): void
    {
        // Limpa o cache de permissões para garantir que as novas permissões sejam carregadas
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Pega o nome da conexão do tenant
        $tenantConnection = config('multitenancy.tenant_database_connection_name');

        // Define o guard name que será usado para todas as permissões do tenant
        $guardName = 'tenant';

        /**
         * Estrutura de permissões por módulo em português.
         *
         * Ações comuns:
         * - visualizar_todos: Ver a listagem do recurso.
         * - visualizar: Ver um recurso específico.
         * - criar: Criar um novo recurso.
         * - atualizar: Atualizar um recurso existente.
         * - excluir: Excluir um recurso.
         */
        $modules = [
            // Módulos Principais de Gestão
            'dashboard' => ['visualizar'],
            'solicitacoes' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir', 'gerenciar_status', 'atribuir', 'supervisionar_juridico'],
            'cidadaos' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir', 'gerenciar_tags', 'gerenciar_notas'],
            'funcionarios' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir', 'gerenciar_perfis'],
            'documentos' => ['visualizar', 'criar', 'excluir'],

            // Módulos de Conteúdo e Serviços Públicos
            'achados_e_perdidos' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'],
            'pessoas_desaparecidas' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir', 'moderar'],
            'vagas_de_emprego' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'],
            'empresas' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'], // Empresas que postam vagas
            'candidaturas' => ['visualizar_todos', 'visualizar', 'atualizar_status'], // Candidaturas para as vagas

            // Módulos Legislativos
            'memoria_legislativa' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'],
            'comissoes' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'],
            'sugestoes_de_lei' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir', 'gerenciar_status'],
            'gabinete_virtual' => ['gerenciar_dashboard', 'visualizar_mensagens', 'responder_mensagens', 'criar'],

            // Módulos de Relacionamento e Comunicação
            'campanhas' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir', 'enviar'],
            'entidades' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'],
            'convenios' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'],

            // Módulos de Administração do Sistema
            'configuracoes' => ['visualizar', 'atualizar'],
            'relatorios' => ['visualizar_geral', 'visualizar_atendimentos', 'visualizar_satisfacao', 'visualizar_cidadaos', 'visualizar_demandas_por_bairro', 'visualizar_analise_de_tendencias', 'visualizar_mapeamento_politico'],
            'usuarios' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir', 'personificar'],
            'perfis' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'], // Roles
            'tipos_servico' => ['visualizar_todos', 'criar', 'atualizar', 'excluir'],
            'servicos' => ['visualizar_todos', 'visualizar', 'criar', 'atualizar', 'excluir'],
            'status_solicitacao' => ['visualizar_todos', 'criar', 'atualizar', 'excluir'],
            'campos_personalizados' => ['visualizar_todos', 'criar', 'atualizar', 'excluir'],
            'logs_de_atividade' => ['visualizar_todos', 'visualizar'],

            // Módulo Portal (visualização pública)
            'portalcidadao' => ['visualizar'],
        ];

        // Itera sobre os módulos e ações para criar cada permissão
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                Permission::on($tenantConnection)->firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => $guardName,
                ]);
            }
        }
    }
}
