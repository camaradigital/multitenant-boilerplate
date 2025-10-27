<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\Lead;
use App\Models\Tenant\User as TenantUser;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\CampanhaComunicacao;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        Log::info('DashboardController: Iniciando carregamento de dados.');

        $cacheKey = 'central_dashboard_data';
        $cacheDuration = 600;

        try {
            $data = Cache::remember($cacheKey, $cacheDuration, function () {
                Log::info("DashboardController: Cache '{$cacheKey}' não encontrado ou expirado. Calculando...");

                $centralConnection = config('database.default');
                $tenantConnection = config('multitenancy.tenant_database_connection_name') ?? 'tenant';
                Log::info("DashboardController: Conexão Central='{$centralConnection}', Conexão Tenant='{$tenantConnection}'");

                $totalLeads = 0;
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

                try {
                    DB::connection($centralConnection)->getPdo();
                    $health['central_database'] = 'up';
                    Log::info("DashboardController: Conexão com BD Central ({$centralConnection}) OK.");
                    try {
                        $totalLeads = Lead::count();
                        Log::info("DashboardController: Total de Leads = {$totalLeads}");
                    } catch (\Exception $e) {
                        Log::error("DashboardController: Erro ao contar Leads: ".$e->getMessage());
                        $totalLeads = -1;
                    }
                } catch (\Exception $e) {
                    Log::error("DashboardController: ERRO FATAL ao conectar ao BD Central ({$centralConnection}): ".$e->getMessage());
                    return ['stats' => ['error' => true], 'health' => $health];
                }

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

                $totalUsers = 0;
                $failedLoginsToday = 0;
                $campaignsSentToday = 0;
                $activeTenantsCount = 0;
                $pendingRequests = 0;
                $completedRequestsToday = 0;

                $startOfToday = Carbon::today()->startOfDay();

                try {
                    $failedLoginsToday = Activity::on($centralConnection)
                        ->where('description', 'failed_login') 
                        ->where('created_at', '>=', $startOfToday)
                        ->count();
                    Log::info("DashboardController: Logins Falhos Hoje (Central) = {$failedLoginsToday}");
                } catch (\Exception $e) {
                    Log::error("DashboardController: Erro ao buscar ActivityLog central: ".$e->getMessage());
                    $failedLoginsToday = -1; 
                }

                $allTenants = Tenant::all();
                $health['total_tenants'] = $allTenants->count();
                Log::info("DashboardController: Encontrados {$health['total_tenants']} tenants para processar.");

                foreach ($allTenants as $tenant) {
                    $tenantIdForLogging = $tenant->id ?? 'ID desconhecido';
                    $tenantNameForLogging = $tenant->name ?? 'Nome desconhecido';
                    Log::info("DashboardController: Processando Tenant ID {$tenantIdForLogging} ({$tenantNameForLogging})...");

                    try {
                        tenancy()->initialize($tenant);
                        DB::connection($tenantConnection)->getPdo();
                        Log::info("DashboardController: Conexão com BD Tenant ID {$tenantIdForLogging} OK.");

                        $health['tenants_databases'][$tenant->id] = [
                            'name' => $tenant->name,
                            'subdomain' => $tenant->subdomain,
                            'status' => 'up'
                        ];
                        $activeTenantsCount++;

                        // 3b. Agrega estatísticas do tenant atual
                        $usersInTenant = TenantUser::count();
                        $totalUsers += $usersInTenant;

                        $campaignsInTenant = CampanhaComunicacao::where('created_at', '>=', $startOfToday)->count();
                        $campaignsSentToday += $campaignsInTenant;

                        $pendingInTenant = SolicitacaoServico::where('status', 'pendente')->count();
                        $pendingRequests += $pendingInTenant;

                        $completedInTenant = SolicitacaoServico::where('status', 'concluido')
                            ->where('updated_at', '>=', $startOfToday)
                            ->count();
                        $completedRequestsToday += $completedInTenant;

                        Log::info("DashboardController: Tenant ID {$tenantIdForLogging} - Users:{$usersInTenant}, Campaigns:{$campaignsInTenant}, Pending:{$pendingInTenant}, Completed:{$completedInTenant}");

                        tenancy()->end();

                    } catch (\Exception $e) {
                        Log::error("DashboardController: ERRO ao processar tenant ID {$tenantIdForLogging}: ".$e->getMessage());
                        if (tenancy()->initialized) {
                            tenancy()->end();
                            Log::info("DashboardController: Contexto do tenant {$tenantIdForLogging} finalizado após erro.");
                        }
                        $health['tenants_databases'][$tenant->id] = [
                            'name' => $tenant->name ?? "Tenant ID {$tenantIdForLogging}",
                            'subdomain' => $tenant->subdomain ?? 'N/A',
                            'status' => 'down'
                        ];
                        $allTenantsUp = false;
                    }
                }

                $health['active_tenants'] = $activeTenantsCount;
                Log::info("DashboardController: Processamento de tenants concluído. Ativos: {$activeTenantsCount}/{$health['total_tenants']}");

                if ($health['central_database'] === 'down') {
                    $health['overall_status'] = 'error';
                } elseif ($health['redis'] === 'down' || !$allTenantsUp) {
                    $health['overall_status'] = 'partial';
                } else {
                    $health['overall_status'] = 'up';
                }
                Log::info("DashboardController: Status Geral da Saúde definido como '{$health['overall_status']}'.");


                $stats = [
                    'totalTenants' => $health['total_tenants'],
                    'tenantsAtivos' => $activeTenantsCount,
                    'totalLeads' => $totalLeads,
                    'totalUsuarios' => $totalUsers,
                    'loginsFalhosHoje' => $failedLoginsToday,
                    'pendingRequests' => $pendingRequests,
                    'completedRequestsToday' => $completedRequestsToday,
                    'error' => false
                ];

                Log::info("DashboardController: Dados calculados:", $stats);
                Log::info("DashboardController: Saúde calculada:", $health);

                return ['stats' => $stats, 'health' => $health];
            });

            if (empty($data) || !isset($data['stats']) || !isset($data['health']) || ($data['stats']['error'] ?? false)) {
                Log::error("DashboardController: Falha ao obter dados do cache ou erro durante o cálculo.");
                $defaultData = [
                    'stats' => [
                        'totalTenants' => 0, 'tenantsAtivos' => 0, 'totalLeads' => 0,
                        'totalUsuarios' => 0, 'loginsFalhosHoje' => 0, 'campanhasEnviadasHoje' => 0,
                        'pendingRequests' => 0, 'completedRequestsToday' => 0, 'error' => true
                    ],
                    'health' => [
                        'app' => 'up', 'central_database' => 'down', 'redis' => 'unknown',
                        'tenants_databases' => [], 'overall_status' => 'error',
                        'active_tenants' => 0, 'total_tenants' => 0,
                    ]
                ];
                $data = $defaultData;
            } else {
                Log::info("DashboardController: Dados obtidos do cache '{$cacheKey}' com sucesso.");
            }

        } catch (\Exception $e) {
            Log::emergency("DashboardController: Exceção não capturada no método index: " . $e->getMessage(), ['exception' => $e]);
            
            $defaultData = [
                'stats' => [
                    'totalTenants' => 0, 'tenantsAtivos' => 0, 'totalLeads' => -1,
                    'totalUsuarios' => 0, 'loginsFalhosHoje' => -1, 'campanhasEnviadasHoje' => 0,
                    'pendingRequests' => 0, 'completedRequestsToday' => 0, 'error' => true
                ],
                'health' => [
                    'app' => 'up', 'central_database' => 'down', 'redis' => 'unknown',
                    'tenants_databases' => [], 'overall_status' => 'error',
                    'active_tenants' => 0, 'total_tenants' => 0,
                ]
            ];
            $data = $defaultData;
        }

        return Inertia::render('Central/Dashboard', [
            'tenantsAtivos' => $data['stats']['tenantsAtivos'] ?? 0,
            'totalLeads' => $data['stats']['totalLeads'] ?? 0,
            'totalUsuarios' => $data['stats']['totalUsuarios'] ?? 0,
            'loginsFalhosHoje' => $data['stats']['loginsFalhosHoje'] ?? 0,
            'saudeSistema' => $data['health'] ?? [
                'overall_status' => 'error', 
                'central_database' => 'down', 
                'redis' => 'unknown',
                'tenants_databases' => [],
                'active_tenants' => 0,
                'total_tenants' => 0
            ],

            'totalTenants' => $data['stats']['totalTenants'] ?? 0,
            'completedRequestsToday' => $data['stats']['completedRequestsToday'] ?? 0,

            'totalUsers' => $data['stats']['totalUsuarios'] ?? 0,
            'failedLoginsToday' => $data['stats']['loginsFalhosHoje'] ?? 0,
        ]);
    }
}
