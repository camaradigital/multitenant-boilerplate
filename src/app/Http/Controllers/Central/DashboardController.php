<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant; // Modelo Tenant (central)
use App\Models\Central\Lead;   // Novo: Modelo Lead (central)
use App\Models\Tenant\User as TenantUser; // Modelo User (tenant)
use App\Models\Tenant\SolicitacaoServico; // Modelo SolicitacaoServico (tenant)
use App\Models\Tenant\CampanhaComunicacao; // Novo: Modelo Campanha (tenant)
use Carbon\Carbon; // Para datas
use Inertia\Inertia; // Para renderizar Vue
use Spatie\Activitylog\Models\Activity; // Para logs
use Illuminate\Support\Facades\Cache; // Novo: Para performance
use Illuminate\Support\Facades\DB;     // Novo: Para checagem de saúde
use Illuminate\Support\Facades\Redis;  // Novo: Para checagem de saúde

class DashboardController extends Controller
{
    /**
     * Exibe a dashboard do Super Admin com dados agregados de todos os tenants.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        // Cache de 10 minutos (600 segundos) para todos os dados do dashboard.
        // Isso resolve o problema de performance (N+1) e evita sobrecarga.
        $data = Cache::remember('central_dashboard_data', 600, function () {

            // --- 1. Dados Centrais e Saúde Inicial ---
            $totalLeads = Lead::count(); // Novo requisito
            $health = [
                'app' => 'up',
                'central_database' => 'down',
                'redis' => 'down',
                'tenants_databases' => [],
                'overall_status' => 'error', // 'up', 'partial', 'error'
            ];
            $allTenantsUp = true;

            // 1a. Saúde Banco Central (assumindo 'mysql' como conexão central)
            try {
                DB::connection('mysql')->getPdo();
                $health['central_database'] = 'up';
            } catch (\Exception $e) {
                report($e); // Loga o erro
                $health['overall_status'] = 'error';
                // Se o BD central falhar, não podemos continuar
                return ['stats' => [], 'health' => $health];
            }

            // 1b. Saúde Redis
            try {
                Redis::ping();
                $health['redis'] = 'up';
            } catch (\Exception $e) {
                report($e);
                $health['redis'] = 'down';
                $allTenantsUp = false; // Saúde parcial
            }

            // --- 2. Inicializa Contadores Agregados ---
            // Novos requisitos
            $totalUsers = 0;
            $failedLoginsToday = 0;
            $campaignsSentToday = 0;
            $activeTenantsCount = 0;
            
            // Requisitos originais (mantidos caso o frontend os use)
            $pendingRequests = 0;
            $completedRequestsToday = 0;

            $startOfToday = Carbon::today()->startOfDay();
            $allTenants = Tenant::all();

            // --- 3. Itera sobre cada tenant (UMA ÚNICA VEZ) ---
            foreach ($allTenants as $tenant) {
                try {
                    $tenant->makeCurrent();

                    // 3a. Verifica a saúde do BD do tenant
                    DB::connection('tenant')->getPdo();
                    $health['tenants_databases'][$tenant->id] = [
                        'name' => $tenant->name,
                        'subdomain' => $tenant->subdomain,
                        'status' => 'up'
                    ];
                    $activeTenantsCount++;

                    // 3b. Agrega estatísticas (só executa se a conexão 3a funcionar)
                    
                    // Total de Usuários
                    $totalUsers += TenantUser::count();

                    // Logins Falhos Hoje (com correção de data)
                    $failedLoginsToday += Activity::where('description', 'failed_login')
                        ->where('created_at', '>=', $startOfToday)
                        ->count();

                    // Campanhas Enviadas Hoje (com correção de data)
                    $campaignsSentToday += CampanhaComunicacao::where('created_at', '>=', $startOfToday)
                        ->count();

                    // Dados do controller original (mantidos)
                    $pendingRequests += SolicitacaoServico::where('status', 'pendente')->count();
                    $completedRequestsToday += SolicitacaoServico::where('status', 'concluido')
                        ->where('updated_at', '>=', $startOfToday) // Correção de data
                        ->count();

                    $tenant->forgetCurrent();
                } catch (\Exception $e) {
                    // Se falhar (ex: BD não existe), loga e continua
                    report($e);
                    tenancy()->end(); // Garante que saiu do contexto do tenant
                    $health['tenants_databases'][$tenant->id] = [
                        'name' => $tenant->name,
                        'subdomain' => $tenant->subdomain,
                        'status' => 'down'
                    ];
                    $allTenantsUp = false; // Saúde parcial
                }
            }

            // --- 4. Define Saúde Geral ---
            if ($health['central_database'] === 'up' && $health['redis'] === 'up' && $allTenantsUp) {
                $health['overall_status'] = 'up';
            } elseif ($health['central_database'] === 'up') {
                $health['overall_status'] = 'partial';
            }
            
            $health['active_tenants'] = $activeTenantsCount;
            $health['total_tenants'] = $allTenants->count();

            // --- 5. Monta o pacote de estatísticas ---
            $stats = [
                'totalTenants' => $allTenants->count(), // Total registrado
                'tenantsAtivos' => $activeTenantsCount,  // Total com BD ok
                'totalLeads' => $totalLeads,
                'totalUsuarios' => $totalUsers,
                'loginsFalhosHoje' => $failedLoginsToday,
                'campanhasEnviadasHoje' => $campaignsSentToday,
                
                // Dados do controller original (opcional)
                'pendingRequests' => $pendingRequests,
                'completedRequestsToday' => $completedRequestsToday,
            ];

            return ['stats' => $stats, 'health' => $health];
        });

        // 6. Passa os dados para o componente Vue
        return Inertia::render('Central/Dashboard', [
            // Dados da nova lista solicitada:
            'tenantsAtivos' => $data['stats']['tenantsAtivos'],
            'totalLeads' => $data['stats']['totalLeads'],
            'campanhasEnviadasHoje' => $data['stats']['campanhasEnviadasHoje'],
            'totalUsuarios' => $data['stats']['totalUsuarios'],
            'loginsFalhosHoje' => $data['stats']['loginsFalhosHoje'],
            'saudeSistema' => $data['health'],

            // Bônus (pode ser útil no frontend)
            'totalTenants' => $data['stats']['totalTenants'],

            // Dados do controller antigo (mantidos caso o frontend já os use)
            'pendingRequests' => $data['stats']['pendingRequests'],
            'completedRequestsToday' => $data['stats']['completedRequestsToday'],

            // (O frontend pode acessar os nomes antigos 'totalUsers' e 'failedLoginsToday'
            // pelos novos nomes 'totalUsuarios' e 'loginsFalhosHoje')
        ]);
    }
}
