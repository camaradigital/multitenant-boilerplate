<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\PortalController;
use App\Http\Controllers\Tenant\SolicitacaoController;
use App\Http\Controllers\Tenant\FuncionarioController;
use App\Http\Controllers\Tenant\TipoServicoController;
use App\Http\Controllers\Tenant\ServicoController;

/*
|--------------------------------------------------------------------------
| Rotas do Tenant
|--------------------------------------------------------------------------
| Todas as rotas neste arquivo são carregadas dentro do grupo de middleware 'web',
| que já inclui a lógica de multitenancy (NeedsTenant).
|
| As rotas de autenticação do Fortify e Jetstream funcionarão automaticamente
| se seus Service Providers forem carregados no contexto do tenant.
*/

// Rota pública do portal do cidadão (não precisa de login)
Route::get('/', [PortalController::class, 'home'])->name('portal.home');

// --- ROTAS PROTEGIDAS (PARA USUÁRIOS LOGADOS) ---
// O middleware 'auth' garante que apenas usuários autenticados acessem estas rotas.
// O Fortify/Jetstream gerencia as telas de login.
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Redireciona o dashboard padrão para a home do portal.
    Route::get('/dashboard', fn() => redirect()->route('portal.home'))->name('dashboard');

    // Rotas do painel administrativo
    Route::prefix('admin')->as('admin.')->group(function() {
        Route::post('solicitacoes', [SolicitacaoController::class, 'store'])
            ->name('solicitacoes.store')->middleware('can:solicitar servicos');

        Route::resource('solicitacoes', SolicitacaoController::class)
            ->except(['create', 'store', 'destroy'])
            ->middleware('can:gerenciar servicos');

        Route::middleware(['can:gerenciar servicos'])->group(function () {
            Route::resource('funcionarios', FuncionarioController::class);
            Route::resource('tipos-servico', TipoServicoController::class);
            Route::resource('servicos', ServicoController::class);
        });
    });
});
