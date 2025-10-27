<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant; // Importa o modelo Tenant da área central
use App\Models\Central\Lead;   // Importa o modelo Lead da área central
use App\Models\Tenant\User as TenantUser; // Alias para o modelo User do tenant
// Removido: App\Models\Tenant\SolicitacaoServico;
use Carbon\Carbon;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;   // Necessário para checagem de saúde
use Illuminate\Support\Facades\Log;  // Necessário para logging
use Illuminate\Support\Facades\Redis; // Necessário para checagem de saúde

class DashboardController extends Controller
{
    /**
     * Exibe a dashboard do Super Admin com dados agregados de todos os tenants.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        Log::info('DashboardController: Iniciando carregamento de dados.');

        // --- 1. Saúde, Conexões e Contadores Centrais ---
        $centralConnection = config('database.default');
        $tenantConnection = config('multitenancy.tenant_database_connection_name') ?? 'tenant';
        
        $health = [
            'app' => 'up',
            'central_database' => 'down',
            'redis' => 'unknown',
            'tenants_databases' => [],
            'overall_status' => 'error',
            'active_tenants' => 0,
            'total_tenants' => 0,
        ];
        $allTenantsUp = true;
        $totalLeads = 0;

        // 1a. Saúde Banco Central e Leads
        try {
            DB::connection($centralConnection)->getPdo();
            $health['central_database'] = 'up';
            Log::info("DashboardController: Conexão com BD Central ({$centralConnection}) OK.");

            // Adicionado: contagem de Leads (necessário para o Vue)
            $totalLeads = Lead::count();
            Log::info("DashboardController: Total de Leads = {$totalLeads}");

        } catch (\Exception $e) {
            Log::error("DashboardController: ERRO FATAL ao conectar ao BD Central ({$centralConnection}): ".$e->getMessage());
            $allTenantsUp = false; // Se central está down, tudo está down
            $totalLeads = -1; // Indicar erro
        }

        // 1b. Saúde Redis
        try {
            $redisClient = config('database.redis.client', 'none');
            if ($redisClient === 'none' || !in_array($redisClient, ['phpredis', 'predis'])) {
                $health['redis'] = 'disabled';
                Log::info("DashboardController: Redis está desabilitado na configuração.");
            } else {
                Redis::ping();
                $health['redis'] = 'up';
                Log::info("DashboardController: Conexão com Redis OK.");
            }
        } catch (\Exception $e) {
            Log::warning("DashboardController: Erro ao conectar ao Redis: ".$e->getMessage());
            $health['redis'] = 'down';
        }

        // --- 2. Inicializa contadores agregados ---
        $totalUsuarios = 0;
        $loginsFalhosHoje = 0;
        // $pendingRequests = 0; // Removido conforme solicitado
        // $completedRequestsToday = 0; // Removido

        $startOfToday = Carbon::today()->startOfDay();

        // --- 3. Itera sobre cada tenant ---
        $allTenants = Tenant::all();
        $health['total_tenants'] = $allTenants->count();

        foreach ($allTenants as $tenant) {
            $tenantStatus = 'down'; // Assume 'down' até que a conexão seja provada

            try {
                // Define o tenant atual (método que você confirmou que funciona)
                $tenant->makeCurrent();

                // Tenta conectar ao banco de dados do tenant
                DB::connection($tenantConnection)->getPdo();
                $tenantStatus = 'up';
                $health['active_tenants']++;
                
                Log::info("DashboardController: Conexão com BD Tenant ID {$tenant->id} OK.");

                // --- Se a conexão for OK, busca os dados ---
                
                // Conta usuários
                $totalUsuarios += TenantUser::count();

                // Conta logins falhos (lógica do seu arquivo anterior)
                $loginsFalhosHoje += Activity::where('description', 'failed_login') 
                    ->whereDate('created_at', $startOfToday)
                    ->count();

                // Lógica de 'pendingRequests' e 'completedRequestsToday' removida

            } catch (\Exception $e) {
                Log::error("DashboardController: ERRO ao processar tenant ID {$tenant->id}: ".$e->getMessage());
                $allTenantsUp = false; // Marca a saúde geral como 'partial'
            
            } finally {
                // **CORREÇÃO CRÍTICA DE BUG**
                // Garante que o contexto do tenant seja limpo, mesmo se o 'try' falhar.
                // Sem isso, uma falha travaria o loop nesse tenant.
                $tenant->forgetCurrent();
            }

            // Adiciona o status deste tenant ao array de saúde
            $health['tenants_databases'][$tenant->id] = [
                'name' => $tenant->name,
                // Assumindo que seu model Tenant tem a coluna 'subdomain' como no Vue
                'subdomain' => $tenant->subdomain ?? ($tenant->domain ?? 'N/A'), 
                'status' => $tenantStatus
            ];
        }

        // --- 4. Define Saúde Geral ---
        if ($health['central_database'] === 'down') {
            $health['overall_status'] = 'error';
        } elseif ($health['redis'] === 'down' || !$allTenantsUp) {
            $health['overall_status'] = 'partial'; // Se BD central ok, mas Redis ou Tenants com problema
        } else {
            $health['overall_status'] = 'up'; // Tudo OK (ou 0 tenants)
        }
        Log::info("DashboardController: Status Geral da Saúde definido como '{$health['overall_status']}'.");

        // --- 5. Passa os dados para o componente Vue ---
        return Inertia::render('Central/Dashboard', [
            
            // Props principais que o Vue está usando
            'tenantsAtivos' => $health['active_tenants'],
            'totalTenants' => $health['total_tenants'],
            'totalLeads' => $totalLeads,
            'totalUsuarios' => $totalUsuarios,
            'loginsFalhosHoje' => $loginsFalhosHoje,
            'saudeSistema' => $health,

            // Props de compatibilidade (definidas no Vue, mas não usadas nos cards)
            'campanhasEnviadasHoje' => 0, // A prop existe no Vue
            'pendingRequests' => 0, // A prop existe no Vue
            'completedRequestsToday' => 0, // A prop existe no Vue

            // Props de Mapeamento Antigo (definidas no Vue)
            'totalUsers' => $totalUsuarios,
            'failedLoginsToday' => $loginsFalhosHoje
        ]);
    }
}
