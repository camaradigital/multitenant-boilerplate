<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant; // Importa o modelo Tenant da área central
use App\Models\Tenant\SolicitacaoServico; // Alias para o modelo User do tenant
use App\Models\Tenant\User as TenantUser; // Importa o modelo SolicitacaoServico do contexto do tenant
use Carbon\Carbon; // Importa o modelo Activity do Spatie Activity Log
use Inertia\Inertia; // Para renderizar componentes Vue com Inertia.js
use Spatie\Activitylog\Models\Activity; // Para trabalhar com datas (ex: "hoje")

class DashboardController extends Controller
{
    /**
     * Exibe a dashboard do Super Admin com dados agregados de todos os tenants.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        // 1. Contagem total de tenants (já centralizada)
        $totalTenants = Tenant::count();

        // 2. Inicializa contadores para dados agregados de todos os tenants
        $totalUsers = 0;
        $pendingRequests = 0;
        $completedRequestsToday = 0;
        $failedLoginsToday = 0;

        // Define a data de início do dia atual para filtros (UTC)
        $startOfToday = Carbon::today()->startOfDay();

        // 3. Itera sobre cada tenant para buscar dados específicos
        $allTenants = Tenant::all(); // Obtém todos os tenants registrados

        foreach ($allTenants as $tenant) {
            // Define o tenant atual para que as consultas usem o banco de dados correto
            // Isso é crucial para o spatie/laravel-multitenancy
            // CONFORME DOCUMENTAÇÃO: Usando o método makeCurrent() na instância do Tenant
            $tenant->makeCurrent();

            // Conta usuários do tenant atual
            // NOTA: O erro "Class 'App\Models\Tenant\User' not found" indica que
            // o arquivo App\Models\Tenant\User.php não está sendo carregado corretamente.
            // Verifique o caminho do arquivo (src/app/Models/Tenant/User.php),
            // o namespace dentro do arquivo (namespace App\Models\Tenant;),
            // e execute 'composer dump-autoload' e 'php artisan optimize:clear'.
            $totalUsers += TenantUser::count(); // Usar TenantUser para o modelo do tenant

            // Conta solicitações pendentes do tenant atual
            $pendingRequests += SolicitacaoServico::where('status', 'pendente')->count();

            // Conta solicitações concluídas HOJE no tenant atual
            $completedRequestsToday += SolicitacaoServico::where('status', 'concluido')
                ->whereDate('updated_at', $startOfToday)
                ->count();

            // Conta tentativas de login falhas HOJE no tenant atual
            // Assumindo que o spatie/laravel-activitylog está configurado para logar logins falhos
            // e que o 'log_name' ou 'description' indica um login falho.
            // Você pode precisar ajustar a condição 'description' ou 'event'
            // baseando-se em como os logins falhos são registrados no seu activity log.
            $failedLoginsToday += Activity::where('log_name', 'default') // Ou outro log_name específico para autenticação
                ->where('description', 'failed_login') // Exemplo: se você loga 'failed_login'
                ->whereDate('created_at', $startOfToday)
                ->count();

            // Volta para o contexto do banco de dados central antes de ir para o próximo tenant
            // CONFORME DOCUMENTAÇÃO: Usando o método forgetCurrent() na instância do Tenant
            $tenant->forgetCurrent();
        }

        // 4. Passa os dados agregados para o componente Vue
        return Inertia::render('Central/Dashboard', [
            'totalTenants' => $totalTenants,
            'totalUsers' => $totalUsers,
            'pendingRequests' => $pendingRequests,
            'completedRequestsToday' => $completedRequestsToday,
            'failedLoginsToday' => $failedLoginsToday,
        ]);
    }
}
