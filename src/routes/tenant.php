<?php

use App\Http\Controllers\Tenant\AchadoEPerdidoDocumentoController;
use App\Http\Controllers\Tenant\ActivityLogController;
use App\Http\Controllers\Tenant\Auth\VerificationController;
use App\Http\Controllers\Tenant\BairroController;
use App\Http\Controllers\Tenant\CampanhaController;
use App\Http\Controllers\Tenant\CandidaturaController;
use App\Http\Controllers\Tenant\CidadaoController;
use App\Http\Controllers\Tenant\CidadaoRelacionamentoController;
use App\Http\Controllers\Tenant\ComissaoController;
use App\Http\Controllers\Tenant\ConvenioController;
use App\Http\Controllers\Tenant\CustomFieldController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DashboardCustomizationController;
use App\Http\Controllers\Tenant\DocumentoController;
use App\Http\Controllers\Tenant\EmpresaController;
use App\Http\Controllers\Tenant\EntidadeController;
use App\Http\Controllers\Tenant\FuncionarioController;
use App\Http\Controllers\Tenant\GabineteVirtualController;
use App\Http\Controllers\Tenant\LegislaturaController;
use App\Http\Controllers\Tenant\MandatoController;
use App\Http\Controllers\Tenant\MapeamentoPoliticoController;
use App\Http\Controllers\Tenant\MemoriaLegislativaController;
use App\Http\Controllers\Tenant\MeuPainelController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Controllers\Tenant\ParametroController;
use App\Http\Controllers\Tenant\PermissionController;
use App\Http\Controllers\Tenant\PesquisaSatisfacaoController;
use App\Http\Controllers\Tenant\PessoaDesaparecidaController;
use App\Http\Controllers\Tenant\PoliticoController;
use App\Http\Controllers\Tenant\PortalCidadao\PortalPessoalSugestaoController;
use App\Http\Controllers\Tenant\PortalController;
use App\Http\Controllers\Tenant\ProfileController;
use App\Http\Controllers\Tenant\RealtimeValidationController;
use App\Http\Controllers\Tenant\RelatorioController;
use App\Http\Controllers\Tenant\RolePermissionController;
use App\Http\Controllers\Tenant\ServicoController;
use App\Http\Controllers\Tenant\SolicitacaoServicoController;
use App\Http\Controllers\Tenant\StatusSolicitacaoController;
use App\Http\Controllers\Tenant\SugestaoProjetoLeiController;
use App\Http\Controllers\Tenant\TagController;
use App\Http\Controllers\Tenant\TipoServicoController;
use App\Http\Controllers\Tenant\VagaController;
use App\Models\Tenant\Bairro;
use App\Models\Tenant\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
// Remova os 'use' dos middlewares que não são mais necessários AQUI no topo
// use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession; // REMOVIDO
// use Spatie\Multitenancy\Http\Middleware\NeedsTenant; // REMOVIDO
use Spatie\Multitenancy\Models\Tenant;

/*
|--------------------------------------------------------------------------
| Rotas do Tenant
|--------------------------------------------------------------------------
|
| Estas rotas são carregadas automaticamente pelo bootstrap/app.php
| quando um domínio de tenant é detectado e já estão dentro
| do grupo de middleware 'tenant'.
|
*/

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
    $bairros = Bairro::orderBy('nome')->get(['id', 'nome']);

    return Inertia::render('Auth/Register', [
        'customFields' => $customFields,
        'bairros' => $bairros,
    ]);
})->name('register');

// --- ROTA DE VALIDAÇÃO DE DADOS ---
Route::post('/validate-field', [RealtimeValidationController::class, 'validateField'])->name('realtime.validate');

// --- ROTAS DE REDEFINIÇÃO DE SENHA E VERIFICAÇÃO DE E-MAIL ---
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');

// Middleware 'auth:tenant' aqui porque precisa saber quem está logado para verificar
Route::get('/email/verify', [VerificationController::class, 'show'])->middleware('auth:tenant')->name('verification.notice');
// Middleware 'signed' e 'throttle' são específicos da rota, devem permanecer
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/email/verification-notification', [VerificationController::class, 'send'])->middleware(['auth:tenant', 'throttle:6,1'])->name('verification.send');

// --- ROTAS PARA TERMOS E POLÍTICA DE PRIVACIDADE ---
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

// Se você usa broadcasting para canais autenticados, mantenha isso
Broadcast::routes(['middleware' => ['auth:tenant']]); // Adicionado middleware auth:tenant aqui

// --- ROTAS AUTENTICADAS ---
// Este grupo interno permanece, pois adiciona a verificação de 'auth' e 'verified'
Route::middleware([
    'auth:tenant', // Garante que o usuário está logado no guard 'tenant'
    config('jetstream.auth_session'), // Middleware do Jetstream/Fortify
    'verified', // Garante que o e-mail foi verificado
])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('tenant.dashboard');

    // --- ROTAS DO CIDADÃO ---
    Route::name('portalcidadao.')->group(function () {
        Route::get('/meu-painel', [MeuPainelController::class, 'index'])->name('meu-painel');
        Route::get('/vagas/{vaga}/candidatar', [CandidaturaController::class, 'create'])->name('candidaturas.create');
        Route::post('/vagas/{vaga}/candidatar', [CandidaturaController::class, 'store'])->name('candidaturas.store');
        Route::get('/solicitacoes/criar', [MeuPainelController::class, 'create'])->name('solicitacoes.create');
        Route::get('/minhas-solicitacoes/{solicitacao}', [SolicitacaoServicoController::class, 'show'])->name('solicitacoes.show');
        Route::post('/solicitacoes', [SolicitacaoServicoController::class, 'store'])->name('solicitacoes.store');
        Route::post('/solicitacoes/{solicitacao}/avaliar', [PesquisaSatisfacaoController::class, 'store'])->name('solicitacoes.avaliar');
        Route::get('/indicacao-projeto', [PortalPessoalSugestaoController::class, 'create'])->name('sugestao.create');
        Route::post('/indicacao-projeto', [PortalPessoalSugestaoController::class, 'store'])->name('sugestao.store');
        Route::get('/indicacao-projeto/sucesso/{protocolo}', [PortalPessoalSugestaoController::class, 'success'])->name('sugestao.success');
        Route::get('/profile/export-data', [ProfileController::class, 'exportData'])->name('profile.export-data');
        Route::post('/profile/anonymize-account', [ProfileController::class, 'anonymizeAccount'])->name('profile.anonymize-account');
        Route::delete('/user', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::prefix('gabinete-virtual')->as('gabinete-virtual.')->group(function () {
            Route::get('/', [GabineteVirtualController::class, 'citizenIndex'])->name('index');
            Route::post('/', [GabineteVirtualController::class, 'storeMensagem'])->name('store');
            Route::get('/{mensagem}', [GabineteVirtualController::class, 'citizenShow'])->name('show');
        });
    });

    // MÓDULO DE NOTIFICAÇÕES
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // --- ROTAS DO PAINEL ADMINISTRATIVO ---
    // O prefixo 'admin' e o nome 'admin.' já restringem o acesso,
    // mas as permissões dentro dos controllers farão a verificação final.
    Route::prefix('admin')->as('admin.')->group(function () {

        // MÓDULO DE ATENDIMENTO
        Route::resource('solicitacoes', SolicitacaoServicoController::class)->parameters(['solicitacoes' => 'solicitacao']);
        Route::post('solicitacoes/{solicitacao}/atender', [SolicitacaoServicoController::class, 'atender'])->name('solicitacoes.atender');
        Route::post('solicitacoes/{solicitacao}/documentos', [DocumentoController::class, 'store'])->name('documentos.store');
        Route::get('documentos/{documento}/download', [DocumentoController::class, 'download'])->name('documentos.download');
        Route::delete('documentos/{documento}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');

        // GABINETE VIRTUAL (ADMIN)
        Route::prefix('gabinete-virtual')->as('gabinete-virtual.')->group(function () {
            Route::get('/', [GabineteVirtualController::class, 'adminIndex'])->name('index');
            Route::get('/{mensagem}', [GabineteVirtualController::class, 'adminShow'])->name('show');
            Route::patch('/{mensagem}/status', [GabineteVirtualController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/{mensagem}/responder', [GabineteVirtualController::class, 'storeResposta'])->name('storeResposta');
        });

        // CAMPANHAS (ADMIN)
        Route::prefix('campanhas')->as('campanhas.')->group(function () {
            Route::get('/', [CampanhaController::class, 'index'])->name('index');
            Route::get('/nova', [CampanhaController::class, 'create'])->name('create');
            Route::post('/', [CampanhaController::class, 'store'])->name('store');
            Route::post('/calcular-publico', [CampanhaController::class, 'calcularPublico'])->name('calcular-publico');
            Route::get('/{campanha}', [CampanhaController::class, 'show'])->name('show');
        });

        // MÓDULO DE GESTÃO DE FUNCIONÁRIOS
        Route::resource('funcionarios', FuncionarioController::class)->except(['create', 'edit']);

        // MÓDULO DE GESTÃO DE CIDADÃOS
        Route::get('/cidadaos', [CidadaoController::class, 'index'])->name('cidadaos.index');
        Route::get('/cidadaos/create', [CidadaoController::class, 'create'])->name('cidadaos.create');
        Route::post('/cidadaos', [CidadaoController::class, 'store'])->name('cidadaos.store');
        Route::get('/cidadaos/{cidadao}/edit', [CidadaoController::class, 'edit'])->name('cidadaos.edit');
        Route::put('/cidadaos/{cidadao}', [CidadaoController::class, 'update'])->name('cidadaos.update');
        Route::delete('/cidadaos/{cidadao}', [CidadaoController::class, 'destroy'])->name('cidadaos.destroy');
        Route::post('/cidadaos/{cidadao}/anonymize', [CidadaoController::class, 'anonymize'])->name('cidadaos.anonymize');
        Route::get('/cidadaos/{cidadao}/exportar-dados', [CidadaoController::class, 'exportData'])->name('cidadaos.export-data');

        Route::get('/cidadaos/{cidadao}', [CidadaoRelacionamentoController::class, 'show'])->name('cidadaos.show');
        Route::post('/cidadaos/{cidadao}/notas', [CidadaoRelacionamentoController::class, 'storeNota'])->name('cidadaos.notas.store');
        Route::post('/cidadaos/{cidadao}/tags', [CidadaoRelacionamentoController::class, 'attachTag'])->name('cidadaos.tags.attach');
        Route::delete('/cidadaos/{cidadao}/tags/{tag}', [CidadaoRelacionamentoController::class, 'detachTag'])->name('cidadaos.tags.detach');

        // MÓDULO DE GESTÃO DE SERVIÇOS
        Route::get('servicos', [ServicoController::class, 'index'])->name('servicos.index');
        Route::post('servicos', [ServicoController::class, 'store'])->name('servicos.store');
        Route::get('servicos/{servico}', [ServicoController::class, 'show'])->name('servicos.show');
        Route::put('servicos/{servico}', [ServicoController::class, 'update'])->name('servicos.update');
        Route::delete('servicos/{servico}', [ServicoController::class, 'destroy'])->name('servicos.destroy');
        Route::get('tipos-servico', [TipoServicoController::class, 'index'])->name('tipos-servico.index');
        Route::post('tipos-servico', [TipoServicoController::class, 'store'])->name('tipos-servico.store');
        Route::get('tipos-servico/{tipoServico}', [TipoServicoController::class, 'show'])->name('tipos-servico.show');
        Route::put('tipos-servico/{tipoServico}', [TipoServicoController::class, 'update'])->name('tipos-servico.update');
        Route::delete('tipos-servico/{tipoServico}', [TipoServicoController::class, 'destroy'])->name('tipos-servico.destroy');

        // MÓDULO DE ACHADOS E PERDIDOS
        Route::resource('achados-e-perdidos-documentos', AchadoEPerdidoDocumentoController::class)->except(['show', 'create', 'edit'])->parameters(['achados-e-perdidos-documentos' => 'achadosEPerdidosDocumento']);
        Route::resource('pessoas-desaparecidas', PessoaDesaparecidaController::class)->except(['show', 'create', 'edit'])->parameters(['pessoas-desaparecidas' => 'pessoaDesaparecida']);
        Route::get('pessoas-desaparecidas/{pessoaDesaparecida}/boletim', [PessoaDesaparecidaController::class, 'downloadBoletim'])->name('pessoas-desaparecidas.downloadBoletim');

        // Rotas para Vagas de Emprego
        Route::resource('empresas', EmpresaController::class);
        Route::resource('vagas', VagaController::class);
        Route::get('vagas/{vaga}/candidaturas', [\App\Http\Controllers\Tenant\CandidaturaController::class, 'index'])->name('vagas.candidaturas.index');
        Route::get('candidaturas/{candidatura}/curriculo', [\App\Http\Controllers\Tenant\CandidaturaController::class, 'downloadCurriculo'])->name('candidaturas.downloadCurriculo');

        // MÓDULO DE ENTIDADES E CONVÉNIOS
        Route::resource('entidades', EntidadeController::class)->except(['show', 'create', 'edit']);
        Route::resource('convenios', ConvenioController::class)->except(['show', 'create', 'edit']);

        // MÓDULO DE MEMÓRIA LEGISLATIVA (ADMIN)
        Route::resource('politicos', PoliticoController::class)->except(['show']);
        Route::resource('legislaturas', LegislaturaController::class);
        Route::post('legislaturas/{legislatura}/mandatos', [MandatoController::class, 'store'])->name('mandatos.store');
        Route::delete('mandatos/{mandato}', [MandatoController::class, 'destroy'])->name('mandatos.destroy');
        Route::resource('comissoes', ComissaoController::class)->except(['create', 'edit', 'show'])->parameters(['comissoes' => 'comissao']);
        Route::post('comissoes/{comissao}/membros', [ComissaoController::class, 'adicionarMembro'])->name('comissoes.membros.store');
        Route::delete('comissoes/{comissao}/membros/{membroId}', [ComissaoController::class, 'removerMembro'])->name('comissoes.membros.destroy');

        // MÓDULO DE RELATÓRIOS
        Route::prefix('relatorios')->name('relatorios.')->group(function () {
            Route::get('/atendimentos', [RelatorioController::class, 'atendimentos'])->name('atendimentos');
            Route::get('/atendimentos/exportar', [RelatorioController::class, 'exportarAtendimentos'])->name('atendimentos.exportar');
            Route::get('/atendimentos/exportar-pdf', [RelatorioController::class, 'exportarAtendimentosPDF'])->name('atendimentos.exportarPDF');
            Route::get('/satisfacao', [RelatorioController::class, 'satisfacao'])->name('satisfacao');
            Route::get('/cidadaos', [RelatorioController::class, 'cidadaos'])->name('cidadaos');
            Route::get('/cidadaos/exportar', [RelatorioController::class, 'exportarCidadaos'])->name('cidadaos.exportar');
            Route::get('/cidadaos/exportar-pdf', [RelatorioController::class, 'exportarCidadaosPDF'])->name('cidadaos.exportarPDF');
            Route::get('/demandas-por-bairro', [RelatorioController::class, 'demandasPorBairro'])->name('demandas-por-bairro');
            Route::get('/analise-de-tendencias', [RelatorioController::class, 'analiseDeTendencias'])->name('analise-de-tendencias');
            Route::get('/mapeamento-politico', [MapeamentoPoliticoController::class, 'index'])->name('mapeamento-politico.index');
        });

        // MÓDULO DE CONFIGURAÇÕES E PARÂMETROS
        Route::get('parametros', [ParametroController::class, 'index'])->name('parametros.index');
        Route::put('parametros', [ParametroController::class, 'update'])->name('parametros.update');
        Route::resource('status-solicitacao', StatusSolicitacaoController::class)->except(['create', 'edit'])->parameters(['status-solicitacao' => 'statusSolicitacao']);
        Route::resource('custom-fields', CustomFieldController::class)->except(['create', 'edit'])->parameters(['custom-fields' => 'customField']);
        Route::get('auditoria', [ActivityLogController::class, 'index'])->name('auditoria.index');
        Route::resource('roles-permissions', RolePermissionController::class)->except(['show', 'create', 'edit'])->parameters(['roles-permissions' => 'rolesPermission']);
        Route::resource('permissions', PermissionController::class)->except(['show', 'create', 'edit']);
        Route::resource('bairros', BairroController::class);
        Route::resource('tags', TagController::class);

        // Rotas para Sugestões de Projetos de Lei (ADMIN)
        Route::prefix('sugestoes')->as('sugestoes.')->group(function () {
            Route::get('/', [SugestaoProjetoLeiController::class, 'index'])->name('index');
            Route::get('/{sugestao}', [SugestaoProjetoLeiController::class, 'show'])->name('show');
            Route::post('/{sugestao}/status', [SugestaoProjetoLeiController::class, 'updateStatus'])->name('updateStatus');
            Route::delete('/{sugestao}', [SugestaoProjetoLeiController::class, 'destroy'])->name('destroy');
        });

        // Rotas para Customização da Dashboard (ADMIN)
        Route::get('/dashboard/customize', [DashboardCustomizationController::class, 'edit'])
            ->name('dashboard.customize.edit');
        Route::put('/dashboard/customize', [DashboardCustomizationController::class, 'update'])
            ->name('dashboard.customize.update');
    }); // Fim do grupo Route::prefix('admin')

    // --- ROTAS DE PERFIL DE UTILIZADOR --- (Ainda dentro do middleware 'auth:tenant', 'verified')
    Route::get('/user/profile', function () {
        return Inertia::render('Profile/Show', [
            'bairros' => Bairro::orderBy('nome')->get(['id', 'nome']),
            'customFields' => CustomField::all(),
        ]);
    })->name('profile.show');

    Route::put('/user/profile-information', function (Request $request) {
        app(UpdatesUserProfileInformation::class)->update(Auth::user(), $request->all());
        return back()->with('success', 'Perfil atualizado com sucesso.');
    })->name('user-profile-information.update');

}); // Fim do grupo Route::middleware(['auth:tenant', ...])


// Rota explícita de logout (FORA do grupo autenticado)
// O middleware 'tenant' já foi aplicado pelo bootstrap/app.php
Route::post('/logout', function (Request $request) {
    return app(\Laravel\Fortify\Contracts\LogoutResponse::class)->toResponse($request);
})->name('logout');
