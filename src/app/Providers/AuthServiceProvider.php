<?php

namespace App\Providers;

// Models
use App\Models\Tenant\CampanhaComunicacao;
use App\Models\Tenant\Candidatura;
use App\Models\Tenant\Documento;
use App\Models\Tenant\GabineteVirtualMensagem;
use App\Models\Tenant\PessoaDesaparecida;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\User;
use App\Models\Tenant\Vaga;
// Policies
use App\Policies\Tenant\CampanhaPolicy;
use App\Policies\Tenant\CandidaturaPolicy;
use App\Policies\Tenant\DashboardPolicy;
use App\Policies\Tenant\DocumentoPolicy;
use App\Policies\Tenant\GabineteVirtualMensagemPolicy;
use App\Policies\Tenant\PessoaDesaparecidaPolicy;
use App\Policies\Tenant\SolicitacaoServicoPolicy;
use App\Policies\Tenant\UserPolicy;
use App\Policies\Tenant\VagaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Documento::class => DocumentoPolicy::class,
        SolicitacaoServico::class => SolicitacaoServicoPolicy::class,
        Vaga::class => VagaPolicy::class,
        Candidatura::class => CandidaturaPolicy::class,
        User::class => UserPolicy::class,
        PessoaDesaparecida::class => PessoaDesaparecidaPolicy::class,
        CampanhaComunicacao::class => CampanhaPolicy::class,
        GabineteVirtualMensagem::class => GabineteVirtualMensagemPolicy::class,
        \App\Models\Tenant\Relatorio::class => \App\Policies\Tenant\RelatorioPolicy::class,
        \App\Models\Tenant\AchadoEPerdidoDocumento::class => \App\Policies\Tenant\AchadoEPerdidoDocumentoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define as permissões para visualizar os diferentes dashboards
        Gate::define('view-cidadao-dashboard', [DashboardPolicy::class, 'viewCidadaoDashboard']);
        Gate::define('view-admin-dashboard', [DashboardPolicy::class, 'viewAdminDashboard']);
    }
}
