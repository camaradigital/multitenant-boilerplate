<?php

namespace App\Providers;

// Models
use App\Models\Central\Tenant;
use App\Models\Tenant\AchadoEPerdidoDocumento;
use App\Models\Tenant\CampanhaComunicacao;
use App\Models\Tenant\Candidatura;
use App\Models\Tenant\Comissao;
use App\Models\Tenant\Convenio;
use App\Models\Tenant\CustomField;
use App\Models\Tenant\Documento;
use App\Models\Tenant\Empresa;
use App\Models\Tenant\Entidade;
use App\Models\Tenant\GabineteVirtualMensagem;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\Permission;
use App\Models\Tenant\PessoaDesaparecida;
use App\Models\Tenant\Politico;
use App\Models\Tenant\Relatorio;
use App\Models\Tenant\Role;
use App\Models\Tenant\Servico;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\SugestaoProjetoLei;
use App\Models\Tenant\TipoServico;
use App\Models\Tenant\User;
use App\Models\Tenant\Vaga;
// Policies
use App\Policies\Tenant\AchadoEPerdidoDocumentoPolicy;
use App\Policies\Tenant\ActivityLogPolicy;
use App\Policies\Tenant\CampanhaPolicy;
use App\Policies\Tenant\CandidaturaPolicy;
use App\Policies\Tenant\ComissaoPolicy;
use App\Policies\Tenant\ConfiguracaoPolicy;
use App\Policies\Tenant\ConvenioPolicy;
use App\Policies\Tenant\CustomFieldPolicy;
use App\Policies\Tenant\DashboardPolicy;
use App\Policies\Tenant\DocumentoPolicy;
use App\Policies\Tenant\EmpresaPolicy;
use App\Policies\Tenant\EntidadePolicy;
use App\Policies\Tenant\GabineteVirtualMensagemPolicy;
use App\Policies\Tenant\LegislaturaPolicy;
use App\Policies\Tenant\PermissionPolicy;
use App\Policies\Tenant\PessoaDesaparecidaPolicy;
use App\Policies\Tenant\PoliticoPolicy;
use App\Policies\Tenant\RelatorioPolicy;
use App\Policies\Tenant\RolePolicy;
use App\Policies\Tenant\ServicoPolicy;
use App\Policies\Tenant\SolicitacaoServicoPolicy;
use App\Policies\Tenant\StatusSolicitacaoPolicy;
use App\Policies\Tenant\SugestaoProjetoLeiPolicy;
use App\Policies\Tenant\TipoServicoPolicy;
use App\Policies\Tenant\UserPolicy;
use App\Policies\Tenant\VagaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Módulos Principais
        Documento::class => DocumentoPolicy::class,
        SolicitacaoServico::class => SolicitacaoServicoPolicy::class,
        User::class => UserPolicy::class,

        // Conteúdo e Serviços
        AchadoEPerdidoDocumento::class => AchadoEPerdidoDocumentoPolicy::class,
        PessoaDesaparecida::class => PessoaDesaparecidaPolicy::class,
        Vaga::class => VagaPolicy::class,
        Empresa::class => EmpresaPolicy::class,
        Candidatura::class => CandidaturaPolicy::class,

        // Legislativo
        MemoriaLegislativa::class => LegislaturaPolicy::class, // Associado ao controller principal
        Legislatura::class => LegislaturaPolicy::class,
        Politico::class => PoliticoPolicy::class,
        Comissao::class => ComissaoPolicy::class,
        SugestaoProjetoLei::class => SugestaoProjetoLeiPolicy::class,

        // Relacionamento e Comunicação
        GabineteVirtualMensagem::class => GabineteVirtualMensagemPolicy::class,
        CampanhaComunicacao::class => CampanhaPolicy::class,
        Entidade::class => EntidadePolicy::class,
        Convenio::class => ConvenioPolicy::class,

        // Administração do Sistema
        Tenant::class => ConfiguracaoPolicy::class, // Para a página de Parâmetros
        Relatorio::class => RelatorioPolicy::class,
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
        TipoServico::class => TipoServicoPolicy::class,
        Servico::class => ServicoPolicy::class,
        StatusSolicitacao::class => StatusSolicitacaoPolicy::class,
        CustomField::class => CustomFieldPolicy::class,
        Activity::class => ActivityLogPolicy::class,
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
