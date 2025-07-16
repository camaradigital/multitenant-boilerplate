<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\DashboardController; // Novo: Importa o DashboardController
use App\Http\Controllers\Central\RolePermissionController; // Novo: Importa o RolePermissionController (para gerenciar permissões globais)
use App\Http\Controllers\Central\LeadController;
use App\Http\Controllers\Central\CampaignController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar as rotas web para sua aplicação.
| Essas rotas são carregadas pelo RouteServiceProvider dentro de um grupo
| que contém o grupo de middleware "web". Agora crie algo ótimo!
|
*/

// --- Rota Pública / Marketing ---
// Rota para a página inicial de marketing do sistema, que renderiza o componente Home.vue.
Route::get('/', function () {
    return Inertia::render('Marketing/Home');
})->name('marketing.home');


// --- Rotas do Painel Super Admin (Domínio Central) ---
// Este grupo de rotas é protegido e acessível apenas por usuários autenticados do sistema central.
// Todas as rotas aqui dentro terão o prefixo de URL '/superadmin' e o prefixo de nome 'central.'.
Route::middleware([
    'auth:web', // Usar o guard 'web' explicitamente para o sistema central (Super Admins)
    config('jetstream.auth_session'),
    'verified',
    // Adicione o middleware 'can' aqui se quiser proteger todas as rotas do superadmin
    // por uma permissão geral, por exemplo, 'can:acessar painel superadmin'
])
    ->prefix('superadmin')
    ->as('central.')
    ->group(function () {
        // Dashboard do Super Admin
        // Aponta para o DashboardController e o componente Vue Central/Dashboard.vue
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:gerenciar tenants'); // Protege a dashboard com a permissão de gerenciar tenants

        // Rotas de recurso para gerenciar os tenants (empresas/clientes).
        // Serão acessíveis via /superadmin/tenants
        // Aqui, habilitamos todas as ações básicas de um CRUD completo para tenants.
        Route::resource('tenants', TenantController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']) // Habilita todas as ações básicas do CRUD
            ->middleware('can:gerenciar tenants'); // Protege com a permissão específica

        // Rotas para gerenciar Papéis e Permissões Globais (do sistema central)
        // Acessível via /superadmin/roles-permissions
        Route::resource('roles-permissions', RolePermissionController::class)
            ->names('roles_permissions') // Define nomes de rota como 'central.roles_permissions.index', etc.
            ->middleware('can:gerenciar permissoes centrais'); // Protege com a permissão específica
        // Rotas para o Painel de Parâmetros
        Route::get('parametros', [\App\Http\Controllers\Tenant\ParametroController::class, 'index'])->name('parametros.index');
        Route::put('parametros', [\App\Http\Controllers\Tenant\ParametroController::class, 'update'])->name('parametros.update');
        // Exemplo de outras rotas do Super Admin que podem ser adicionadas futuramente:
        // Route::get('/relatorios-gerais', [GeneralReportController::class, 'index'])->name('reports.general');
        // Route::get('/configuracoes-sistema', [SystemSettingsController::class, 'index'])->name('settings.system');
        Route::resource('leads', LeadController::class)->except(['show']);
        Route::get('campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns/send', [CampaignController::class, 'send'])->name('campaigns.send');
    });
