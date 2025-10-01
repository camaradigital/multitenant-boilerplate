<?php

use App\Http\Controllers\Tenant\AchadoEPerdidoDocumentoController;
use App\Http\Controllers\Tenant\ActivityLogController;
use App\Http\Controllers\Tenant\Auth\VerificationController;
use App\Http\Controllers\Tenant\CandidaturaController;
use App\Http\Controllers\Tenant\CidadaoController;
use App\Http\Controllers\Tenant\ConvenioController;
use App\Http\Controllers\Tenant\CustomFieldController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DocumentoController;
use App\Http\Controllers\Tenant\EmpresaController;
use App\Http\Controllers\Tenant\EntidadeController;
use App\Http\Controllers\Tenant\FuncionarioController;
use App\Http\Controllers\Tenant\LegislaturaController;
use App\Http\Controllers\Tenant\MandatoController;
use App\Http\Controllers\Tenant\MemoriaLegislativaController;
use App\Http\Controllers\Tenant\MeuPainelController;
use App\Http\Controllers\Tenant\ParametroController;
use App\Http\Controllers\Tenant\PermissionController;
use App\Http\Controllers\Tenant\PesquisaSatisfacaoController;
use App\Http\Controllers\Tenant\PessoaDesaparecidaController;
use App\Http\Controllers\Tenant\PoliticoController;
use App\Http\Controllers\Tenant\PortalController;
use App\Http\Controllers\Tenant\ProfileController;
use App\Http\Controllers\Tenant\RealtimeValidationController;
use App\Http\Controllers\Tenant\RelatorioController;
use App\Http\Controllers\Tenant\RolePermissionController;
use App\Http\Controllers\Tenant\ServicoController;
use App\Http\Controllers\Tenant\SolicitacaoServicoController;
use App\Http\Controllers\Tenant\StatusSolicitacaoController;
use App\Http\Controllers\Tenant\TipoServicoController;
use App\Http\Controllers\Tenant\VagaController;
use App\Models\Tenant\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;
use Spatie\Multitenancy\Models\Tenant;

/*
|--------------------------------------------------------------------------
| Rotas do Tenant
|--------------------------------------------------------------------------
*/

Route::middleware([
    NeedsTenant::class,
    'web',
    EnsureValidTenantSession::class,
])->group(function () {

    // --- ROTAS PÚBLICAS DO PORTAL ---
    Route::get('/', [PortalController::class, 'home'])->name('portal.home');
    Route::get('/documentos-perdidos', [PortalController::class, 'achadosEPerdidos'])->name('portal.achados-e-perdidos');
    Route::get('/pessoas-desaparecidas', [PortalController::class, 'pessoasDesaparecidas'])->name('portal.pessoas-desaparecidas');
    Route::get('/memoria-legislativa', [MemoriaLegislativaController::class, 'show'])->name('portal.memoria-legislativa');
    Route::get('/vagas', [VagaController::class, 'indexPublic'])->name('portal.vagas.index');
    Route::get('/vagas/{vaga}', [VagaController::class, 'showPublic'])->name('portal.vagas.show');

    // --- Rota para a página de registro ---
    Route::get('/register', function () {
        $customFields = CustomField::all();

        return Inertia::render('Auth/Register', [
            'customFields' => $customFields,
        ]);
    })->name('register');

    // --- ROTA DE VALIDAÇÃO DE DADOS (LOCAL CORRETO) ---
    Route::post('/validate-field', [RealtimeValidationController::class, 'validateField'])->name('realtime.validate');

    // --- ROTAS DE REDEFINIÇÃO DE SENHA E VERIFICAÇÃO DE E-MAIL |  NÃO PODE EXCLUIR ---
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');

    Route::get('/email/verify', [VerificationController::class, 'show'])->middleware('auth:tenant')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'send'])->middleware(['auth:tenant', 'throttle:6,1'])->name('verification.send');

    // --- ROTAS PARA TERMOS E POLÍTICA DE PRIVACIDADE (VERSÃO FINAL) ---
    Route::get('/terms-of-service', function () {
        $tenant = Tenant::current();

        return Inertia::render('Policy', [
            'tenantName' => $tenant?->name,
            'lastUpdated' => $tenant?->updated_at->translatedFormat('d \d\e F \d\e Y'),
            'content' => $tenant?->terms_of_service ?? '<h1>Termos de Serviço não definidos.</h1><p>O administrador pode definir este conteúdo no painel de parâmetros.</p>',
            'title' => 'Termos de Serviço',
        ]);
    })->name('terms.show');

    Route::get('/privacy-policy', function () {
        $tenant = Tenant::current();

        return Inertia::render('Policy', [
            'tenantName' => $tenant?->name,
            'lastUpdated' => $tenant?->updated_at->translatedFormat('d \d\e F \d\e Y'),
            'content' => $tenant?->privacy_policy ?? '<h1>Política de Privacidade não definida.</h1><p>O administrador pode definir este conteúdo no painel de parâmetros.</p>',
            'title' => 'Política de Privacidade',
        ]);
    })->name('policy.show');

    Broadcast::routes();

    // --- ROTAS AUTENTICADAS ---
    Route::middleware([
        'auth:tenant',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {

        // --- CORREÇÃO APLICADA ---
        // A rota do dashboard agora aponta diretamente para o DashboardController.
        // O próprio controller tem a lógica para decidir qual view mostrar (Cidadão ou Admin),
        // e o Laravel irá injetar as dependências (como o RelatorioService) corretamente.
        Route::get('/dashboard', DashboardController::class)->name('tenant.dashboard');

        // --- ROTAS DO CIDADÃO ---
        Route::get('/meu-painel', [MeuPainelController::class, 'index'])->name('portal.meu-painel');
        // Rotas para Candidaturas
        Route::get('/vagas/{vaga}/candidatar', [CandidaturaController::class, 'create'])->name('candidaturas.create');
        Route::post('/vagas/{vaga}/candidatar', [CandidaturaController::class, 'store'])->name('candidaturas.store');
        // Rotas para Solicitações
        Route::get('/solicitacoes/criar', [MeuPainelController::class, 'create'])->name('portal.solicitacoes.create');
        Route::get('/minhas-solicitacoes/{solicitacao}', [SolicitacaoServicoController::class, 'show'])->name('portal.solicitacoes.show');
        Route::post('/solicitacoes', [SolicitacaoServicoController::class, 'store'])->name('portal.solicitacoes.store');
        Route::post('/solicitacoes/{solicitacao}/avaliar', [PesquisaSatisfacaoController::class, 'store'])->name('portal.solicitacoes.avaliar');
        Route::get('/profile/export-data', [ProfileController::class, 'exportData'])->name('profile.export-data');
        Route::post('/profile/anonymize-account', [ProfileController::class, 'anonymizeAccount'])->name('profile.anonymize-account');
        Route::delete('/user', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // --- ROTAS DO PAINEL ADMINISTRATIVO ---
        Route::prefix('admin')->as('admin.')->group(function () {

            // MÓDULO DE ATENDIMENTO
            Route::resource('solicitacoes', SolicitacaoServicoController::class)->parameters(['solicitacoes' => 'solicitacao']);
            Route::post('solicitacoes/{solicitacao}/documentos', [DocumentoController::class, 'store'])->name('documentos.store');
            Route::get('documentos/{documento}/download', [DocumentoController::class, 'download'])->name('documentos.download');
            Route::delete('documentos/{documento}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');

            // MÓDULO DE GESTÃO DE UTILIZADORES
            Route::middleware('can:gerenciar funcionarios')->group(function () {
                Route::resource('funcionarios', FuncionarioController::class)->except(['create', 'edit']);
            });
            Route::middleware('can:gerenciar cidadaos')->group(function () {
                Route::resource('cidadaos', CidadaoController::class);
                Route::post('/cidadaos/{cidadao}/anonymize', [CidadaoController::class, 'anonymize'])->name('cidadaos.anonymize');
                Route::get('/cidadaos/{cidadao}/exportar-dados', [CidadaoController::class, 'exportData'])->name('cidadaos.export-data');
            });

            // MÓDULO DE GESTÃO DE SERVIÇOS
            Route::middleware('can:gerenciar servicos')->group(function () {
                // Rotas para 'Serviços' (definidas manualmente para garantir funcionamento)
                Route::get('servicos', [ServicoController::class, 'index'])->name('servicos.index');
                Route::post('servicos', [ServicoController::class, 'store'])->name('servicos.store');
                Route::get('servicos/{servico}', [ServicoController::class, 'show'])->name('servicos.show');
                Route::put('servicos/{servico}', [ServicoController::class, 'update'])->name('servicos.update');
                Route::delete('servicos/{servico}', [ServicoController::class, 'destroy'])->name('servicos.destroy');

                // Rotas para 'Tipos de Serviço' (definidas manualmente para garantir funcionamento)
                Route::get('tipos-servico', [TipoServicoController::class, 'index'])->name('tipos-servico.index');
                Route::post('tipos-servico', [TipoServicoController::class, 'store'])->name('tipos-servico.store');
                Route::get('tipos-servico/{tipoServico}', [TipoServicoController::class, 'show'])->name('tipos-servico.show');
                Route::put('tipos-servico/{tipoServico}', [TipoServicoController::class, 'update'])->name('tipos-servico.update');
                Route::delete('tipos-servico/{tipoServico}', [TipoServicoController::class, 'destroy'])->name('tipos-servico.destroy');
            });

            // MÓDULO DE ACHADOS E PERDIDOS
            Route::middleware('can:gerenciar achados e perdidos')->group(function () {
                Route::resource('achados-e-perdidos-documentos', AchadoEPerdidoDocumentoController::class)->except(['show', 'create', 'edit'])->parameters(['achados-e-perdidos-documentos' => 'achadosEPerdidosDocumento']);
                Route::resource('pessoas-desaparecidas', PessoaDesaparecidaController::class)->except(['show', 'create', 'edit'])->parameters(['pessoas-desaparecidas' => 'pessoaDesaparecida']);
                Route::get('pessoas-desaparecidas/{pessoaDesaparecida}/boletim', [PessoaDesaparecidaController::class, 'downloadBoletim'])->name('pessoas-desaparecidas.downloadBoletim');
            });

            // Rotas para Vagas de Emprego
            Route::middleware('can:gerenciar vagas de emprego')->group(function () {
                Route::resource('empresas', EmpresaController::class);
                Route::resource('vagas', VagaController::class);
                Route::get('vagas/{vaga}/candidaturas', [\App\Http\Controllers\Tenant\CandidaturaController::class, 'index'])->name('vagas.candidaturas.index');
                Route::get('candidaturas/{candidatura}/curriculo', [\App\Http\Controllers\Tenant\CandidaturaController::class, 'downloadCurriculo'])->name('candidaturas.downloadCurriculo');
            });

            // MÓDULO DE ENTIDADES E CONVÉNIOS
            Route::middleware('can:gerenciar entidades')->group(function () {
                Route::resource('entidades', EntidadeController::class)->except(['show', 'create', 'edit']);
                Route::resource('convenios', ConvenioController::class)->except(['show', 'create', 'edit']);
            });

            // MÓDULO DE MEMÓRIA LEGISLATIVA (ADMIN)
            Route::middleware('can:gerenciar memoria')->group(function () {
                Route::resource('politicos', PoliticoController::class)->except(['show']);
                Route::resource('legislaturas', LegislaturaController::class);
                Route::post('legislaturas/{legislatura}/mandatos', [MandatoController::class, 'store'])->name('mandatos.store');
                Route::delete('mandatos/{mandato}', [MandatoController::class, 'destroy'])->name('mandatos.destroy');
            });

            // MÓDULO DE RELATÓRIOS
            Route::middleware('can:visualizar relatorios')->prefix('relatorios')->name('relatorios.')->group(function () {
                Route::get('/atendimentos', [RelatorioController::class, 'atendimentos'])->name('atendimentos');
                Route::get('/atendimentos/exportar', [RelatorioController::class, 'exportarAtendimentos'])->name('atendimentos.exportar');
                Route::get('/atendimentos/exportar-pdf', [RelatorioController::class, 'exportarAtendimentosPDF'])->name('atendimentos.exportarPDF');
                Route::get('/satisfacao', [RelatorioController::class, 'satisfacao'])->name('satisfacao');
                Route::get('/cidadaos', [RelatorioController::class, 'cidadaos'])->name('cidadaos');
                Route::get('/cidadaos/exportar', [RelatorioController::class, 'exportarCidadaos'])->name('cidadaos.exportar');
                Route::get('/cidadaos/exportar-pdf', [RelatorioController::class, 'exportarCidadaosPDF'])->name('cidadaos.exportarPDF');

                // --- NOVAS ROTAS ESTRATÉGICAS ---
                Route::get('/demandas-por-bairro', [RelatorioController::class, 'demandasPorBairro'])->name('demandas-por-bairro');
                Route::get('/analise-de-tendencias', [RelatorioController::class, 'analiseDeTendencias'])->name('analise-de-tendencias');
            });

            // MÓDULO DE CONFIGURAÇÕES E PARÂMETROS
            Route::middleware('can:gerenciar parametros')->group(function () {
                Route::get('parametros', [ParametroController::class, 'index'])->name('parametros.index');
                Route::put('parametros', [ParametroController::class, 'update'])->name('parametros.update');
                Route::resource('status-solicitacao', StatusSolicitacaoController::class)->except(['create', 'edit'])->parameters(['status-solicitacao' => 'statusSolicitacao']);
                Route::resource('custom-fields', CustomFieldController::class)->except(['create', 'edit']);
                Route::get('auditoria', [ActivityLogController::class, 'index'])->name('auditoria.index');
                Route::resource('roles-permissions', RolePermissionController::class)->except(['show', 'create', 'edit'])->parameters(['roles-permissions' => 'rolesPermission']);
                Route::resource('permissions', PermissionController::class)->except(['show', 'create', 'edit']);
            });
        });

        // --- ROTAS DE PERFIL DE UTILIZADOR ---
        Route::get('/user/profile', function () {
            return Inertia::render('Profile/Show');
        })->name('profile.show');

        Route::put('/user/profile-information', function (Request $request) {
            app(UpdatesUserProfileInformation::class)->update(Auth::user(), $request->all());

            return back()->with('success', 'Perfil atualizado com sucesso.');
        })->name('user-profile-information.update');
    });

    // Rota explícita de logout para garantir middleware tenant
    Route::post('/logout', function (Request $request) {
        return app(\Laravel\Fortify\Contracts\LogoutResponse::class)->toResponse($request);
    })->name('logout');

});
