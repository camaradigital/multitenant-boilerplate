import {
    LayoutDashboard,
    FileText,
    Users,
    UserCheck,
    Wrench,
    FolderKanban,
    Settings,
    SlidersHorizontal,
    ClipboardCheck,
    ListChecks,
    History,
    Handshake,
    Library,
    FileBadge,
    UserSearch,
    KeyRound,
    ShieldCheck,
    Landmark,
    CalendarDays,
    UserSquare,
    BarChart3,
    Star,
    // --- Ícones para o novo módulo ---
    Briefcase,
    Building2,
    // --- ÍCONES NOVOS PARA RELATÓRIOS ESTRATÉGICOS ---
    MapPin,
    TrendingUp,
} from 'lucide-vue-next';

/**
 * @description Navegação principal.
 */
const coreNav = [
    {
        name: 'Dashboard',
        href: route('tenant.dashboard'),
        current: 'tenant.dashboard',
        icon: LayoutDashboard,
        permission: null // Acessível a todos
    },
];

/**
 * @description Rotas públicas visíveis apenas para o Cidadão logado.
 */
const publicNav = [
    {
        name: 'Acesso Rápido',
        icon: Landmark,
        permission: 'ver portal', // <- A permissão para Cidadãos
        children: [
            { name: 'Documentos Perdidos', href: route('portal.achados-e-perdidos'), current: 'portal.achados-e-perdidos', icon: FileBadge, permission: null },
            { name: 'Pessoas Desaparecidas', href: route('portal.pessoas-desaparecidas'), current: 'portal.pessoas-desaparecidas', icon: UserSearch, permission: null },
            { name: 'Memória Legislativa', href: route('portal.memoria-legislativa'), current: 'portal.memoria-legislativa', icon: Landmark, permission: null },
            { name: 'Vagas de Emprego', href: route('portal.vagas.index'), current: 'portal.vagas.*', icon: Briefcase, permission: null },
        ]
    }
];

/**
 * @description Rotas de gerenciamento operacional do dia a dia.
 */
const managementNav = [
    {
        name: 'Gerenciamento',
        icon: Settings,
        permission: null,
        children: [
            { name: 'Solicitações', href: route('admin.solicitacoes.index'), current: 'admin.solicitacoes.*', icon: FileText, permission: 'ver solicitacoes' },
            { name: 'Funcionários', href: route('admin.funcionarios.index'), current: 'admin.funcionarios.*', icon: Users, permission: 'gerenciar funcionarios' },
            { name: 'Cidadãos', href: route('admin.cidadaos.index'), current: 'admin.cidadaos.*', icon: UserCheck, permission: 'gerenciar cidadaos' },
            { name: 'Serviços', href: route('admin.servicos.index'), current: 'admin.servicos.*', icon: Wrench, permission: 'gerenciar servicos' },
            { name: 'Tipos de Serviço', href: route('admin.tipos-servico.index'), current: 'admin.tipos-servico.*', icon: FolderKanban, permission: 'gerenciar servicos' },
            { name: 'Entidades', href: route('admin.entidades.index'), current: 'admin.entidades.*', icon: Library, permission: 'gerenciar entidades' },
            { name: 'Convênios', href: route('admin.convenios.index'), current: 'admin.convenios.*', icon: Handshake, permission: 'gerenciar entidades' },
        ]
    },
];

/**
 * @description Rotas para o novo módulo de Vagas de Emprego.
 */
const jobsNav = [
    {
        name: 'Vagas de Emprego',
        icon: Briefcase,
        permission: 'gerenciar vagas de emprego',
        children: [
            { name: 'Vagas', href: route('admin.vagas.index'), current: 'admin.vagas.*', icon: FileText, permission: 'gerenciar vagas de emprego' },
            { name: 'Empresas', href: route('admin.empresas.index'), current: 'admin.empresas.*', icon: Building2, permission: 'gerenciar vagas de emprego' },
        ]
    }
];

/**
 * @description Rotas para o módulo de Memória Legislativa.
 */
const memoriaLegislativaNav = [
    {
        name: 'Memória Legislativa',
        icon: Landmark,
        permission: 'gerenciar memoria',
        children: [
            { name: 'Legislaturas', href: route('admin.legislaturas.index'), current: 'admin.legislaturas.*', icon: CalendarDays, permission: 'gerenciar memoria' },
            { name: 'Políticos', href: route('admin.politicos.index'), current: 'admin.politicos.*', icon: UserSquare, permission: 'gerenciar memoria' },
        ]
    }
];

/**
 * @description Rotas para o módulo de Achados e Perdidos.
 */
const lostAndFoundNav = [
    {
        name: 'Achados e Perdidos',
        icon: FileBadge,
        permission: 'gerenciar achados e perdidos',
        children: [
            { name: 'Documentos', href: route('admin.achados-e-perdidos-documentos.index'), current: 'admin.achados-e-perdidos-documentos.*', icon: FileBadge, permission: 'gerenciar achados e perdidos' },
            { name: 'Pessoas Desaparecidas', href: route('admin.pessoas-desaparecidas.index'), current: 'admin.pessoas-desaparecidas.*', icon: UserSearch, permission: 'gerenciar achados e perdidos' },
        ]
    }
];

/**
 * @description Rotas para a seção de Relatórios.
 */
const reportsNav = [
    {
        name: 'Relatórios',
        icon: BarChart3,
        permission: 'visualizar relatorios',
        children: [
            { name: 'Atendimentos', href: route('admin.relatorios.atendimentos'), current: 'admin.relatorios.atendimentos', icon: ClipboardCheck, permission: 'visualizar relatorios' },
            { name: 'Satisfação', href: route('admin.relatorios.satisfacao'), current: 'admin.relatorios.satisfacao', icon: Star, permission: 'visualizar relatorios' },
            { name: 'Cidadãos', href: route('admin.relatorios.cidadaos'), current: 'admin.relatorios.cidadaos', icon: Users, permission: 'visualizar relatorios' },
            // --- NOVOS RELATÓRIOS ---
            { name: 'Mapeamento de Demandas', href: route('admin.relatorios.demandas-por-bairro'), current: 'admin.relatorios.demandas-por-bairro', icon: MapPin, permission: 'viewDemandasPorBairro' },
            { name: 'Análise de Tendências', href: route('admin.relatorios.analise-de-tendencias'), current: 'admin.relatorios.analise-de-tendencias', icon: TrendingUp, permission: 'viewAnaliseDeTendencias' },
        ]
    },
];

/**
 * @description Rotas de configuração do sistema.
 */
const settingsNav = [
    {
        name: 'Parâmetros',
        icon: SlidersHorizontal,
        permission: 'gerenciar parametros',
        children: [
            { name: 'Gerais', href: route('admin.parametros.index'), current: 'admin.parametros.*', icon: Settings, permission: 'gerenciar parametros' },
            { name: 'Status de Solicitação', href: route('admin.status-solicitacao.index'), current: 'admin.status-solicitacao.*', icon: ClipboardCheck, permission: 'gerenciar parametros' },
            { name: 'Campos Personalizados', href: route('admin.custom-fields.index'), current: 'admin.custom-fields.*', icon: ListChecks, permission: 'gerenciar parametros' },
            { name: 'Papéis', href: route('admin.roles-permissions.index'), current: 'admin.roles-permissions.*', icon: ShieldCheck, permission: 'gerenciar parametros' },
            { name: 'Permissões', href: route('admin.permissions.index'), current: 'admin.permissions.*', icon: KeyRound, permission: 'gerenciar parametros' },
            { name: 'Auditoria', href: route('admin.auditoria.index'), current: 'admin.auditoria.index', icon: History, permission: 'gerenciar parametros' },
        ]
    },
];

// Montagem final do menu de navegação
export const navigation = [
    ...coreNav,
    ...publicNav,
    ...managementNav,
    ...jobsNav,
    ...memoriaLegislativaNav,
    ...lostAndFoundNav,
    ...reportsNav,
    ...settingsNav,
];
