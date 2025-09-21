<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\RolePermissionController;
use App\Http\Controllers\Central\LeadController;
use App\Http\Controllers\Central\CampaignController;
use App\Http\Controllers\Central\CnpjController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Central\ContactController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Rota Pública / Marketing ---
Route::get('/', function () {
    return Inertia::render('Marketing/Home');
})->name('marketing.home');

// --- Rota de email contato ---
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// --- Rotas do Painel Super Admin (Domínio Central) ---
Route::middleware([
    'auth:web',
    config('jetstream.auth_session'),
    'verified',
])
    ->prefix('superadmin')
    ->as('central.')
    ->group(function () {
        // Dashboard do Super Admin
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:gerenciar tenants');

        // Rotas de recurso para gerenciar os tenants
        Route::resource('tenants', TenantController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
            ->middleware('can:gerenciar tenants');

        // Rotas para gerenciar Papéis e Permissões Globais
        Route::resource('roles-permissions', RolePermissionController::class)
            ->names('roles_permissions')
            ->middleware('can:gerenciar permissoes centrais');

        // Outras rotas do Super Admin
        Route::resource('leads', LeadController::class)->except(['show']);
        Route::get('campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns/send', [CampaignController::class, 'send'])->name('campaigns.send');

        Route::get('/health', [HealthCheckController::class, 'index']);
    });

    Route::get('/unsubscribe/{lead}', function (\App\Models\Central\Lead $lead) {
        // Aqui você adicionaria a lógica para marcar o lead como "descadastrado"
        // Exemplo:
        // $lead->update(['subscribed' => false]);

        return 'Sua inscrição foi cancelada com sucesso.';
    })->name('unsubscribe')->middleware('signed');

/*
|--------------------------------------------------------------------------
| Rotas de API Interna (Acessíveis via Fetch/JS)
|--------------------------------------------------------------------------
*/
Route::get('/consultar-cnpj/{cnpj}', [CnpjController::class, 'consultarCnpj'])
    ->name('api.cnpj.consulta'); // Agora o nome e a URL estão corretos!
