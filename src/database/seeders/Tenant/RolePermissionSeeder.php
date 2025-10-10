<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa o cache para garantir que as novas atribuições sejam carregadas
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenantConnection = config('multitenancy.tenant_database_connection_name');

        // 1. Busca todos as permissões da conexão do tenant
        $allPermissions = Permission::on($tenantConnection)->get();

        // 2. Cria os papéis (ou os busca se já existirem)
        $roleCidadao = Role::on($tenantConnection)->firstOrCreate(['name' => 'Cidadao', 'guard_name' => 'tenant']);
        $roleFuncionario = Role::on($tenantConnection)->firstOrCreate(['name' => 'Funcionario', 'guard_name' => 'tenant']);
        $roleAdvogado = Role::on($tenantConnection)->firstOrCreate(['name' => 'Advogado Coordenador', 'guard_name' => 'tenant']);
        $roleAdmin = Role::on($tenantConnection)->firstOrCreate(['name' => 'Admin Tenant', 'guard_name' => 'tenant']);

        // 3. Define e atribui as permissões para cada papel

        // Papel: Cidadao
        // Acesso apenas às funcionalidades do portal do cidadão.
        $cidadaoPermissions = [
            'dashboard.visualizar',
            'portalcidadao.visualizar',
            'solicitacoes.criar',
            'sugestoes_de_lei.criar',
            'gabinete_virtual.criar',
            'documentos.criar',
            'documentos.visualizar',
            'documentos.excluir',
        ];
        $this->assignPermissions($roleCidadao, $allPermissions, $cidadaoPermissions);

        // Papel: Funcionario
        // Permissões operacionais do dia a dia, sem acesso a configurações críticas do sistema.
        $funcionarioPermissions = [
            'dashboard.visualizar',
            // Solicitações (sem supervisão jurídica)
            'solicitacoes.visualizar_todos', 'solicitacoes.visualizar', 'solicitacoes.criar', 'solicitacoes.atualizar', 'solicitacoes.excluir', 'solicitacoes.gerenciar_status', 'solicitacoes.atribuir',
            // Cidadãos
            'cidadaos.visualizar_todos', 'cidadaos.visualizar', 'cidadaos.criar', 'cidadaos.atualizar', 'cidadaos.excluir', 'cidadaos.gerenciar_tags', 'cidadaos.gerenciar_notas',
            // Achados e Perdidos & Pessoas Desaparecidas
            'achados_e_perdidos.visualizar_todos', 'achados_e_perdidos.criar', 'achados_e_perdidos.atualizar', 'achados_e_perdidos.excluir',
            'pessoas_desaparecidas.visualizar_todos', 'pessoas_desaparecidas.criar', 'pessoas_desaparecidas.atualizar', 'pessoas_desaparecidas.excluir', 'pessoas_desaparecidas.moderar',
            // Empregos
            'vagas_de_emprego.visualizar_todos', 'vagas_de_emprego.criar', 'vagas_de_emprego.atualizar', 'vagas_de_emprego.excluir',
            'empresas.visualizar_todos', 'empresas.criar', 'empresas.atualizar', 'empresas.excluir',
            'candidaturas.visualizar_todos', 'candidaturas.atualizar_status',
            // Legislativo
            'memoria_legislativa.visualizar_todos', 'memoria_legislativa.criar', 'memoria_legislativa.atualizar', 'memoria_legislativa.excluir',
            'comissoes.visualizar_todos', 'comissoes.criar', 'comissoes.atualizar', 'comissoes.excluir',
            'sugestoes_de_lei.visualizar_todos', 'sugestoes_de_lei.gerenciar_status', 'sugestoes_de_lei.excluir',
            // Comunicação
            'gabinete_virtual.visualizar_mensagens', 'gabinete_virtual.responder_mensagens',
            'campanhas.visualizar_todos', 'campanhas.criar', 'campanhas.enviar', 'campanhas.excluir',
            // Relacionamento
            'entidades.visualizar_todos', 'entidades.criar', 'entidades.atualizar', 'entidades.excluir',
            'convenios.visualizar_todos', 'convenios.criar', 'convenios.atualizar', 'convenios.excluir',
            // Relatórios
            'relatorios.visualizar_geral', 'relatorios.visualizar_demandas_por_bairro', 'relatorios.visualizar_analise_de_tendencias', 'relatorios.visualizar_atendimentos', 'relatorios.visualizar_satisfacao', 'relatorios.visualizar_cidadaos', 'relatorios.visualizar_mapeamento_politico',
            // Documentos
            'documentos.visualizar', 'documentos.criar', 'documentos.excluir',
        ];
        $this->assignPermissions($roleFuncionario, $allPermissions, $funcionarioPermissions);

        // Papel: Advogado Coordenador
        // Herda as permissões de um funcionário e adiciona a capacidade de supervisionar.
        $advogadoPermissions = array_merge($funcionarioPermissions, [
            'solicitacoes.supervisionar_juridico', // PERMISSÃO ESPECIAL
        ]);
        $this->assignPermissions($roleAdvogado, $allPermissions, $advogadoPermissions);

        // Papel: Admin Tenant
        // Sincroniza TODAS as permissões, EXCETO as que são exclusivas do cidadão.
        $citizenOnlyPermissions = [
            'portalcidadao.visualizar',
            'sugestoes_de_lei.criar',
            'gabinete_virtual.criar',
        ];

        $adminPermissions = $allPermissions->reject(function ($permission) use ($citizenOnlyPermissions) {
            return in_array($permission->name, $citizenOnlyPermissions);
        });

        $roleAdmin->syncPermissions($adminPermissions);
    }

    /**
     * Função auxiliar para filtrar e atribuir permissões de forma segura.
     */
    private function assignPermissions(Role $role, Collection $allPermissions, array $permissionNames): void
    {
        $permissionsToAssign = $allPermissions->whereIn('name', $permissionNames);
        $role->syncPermissions($permissionsToAssign);
    }
}
