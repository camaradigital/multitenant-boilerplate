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
    Briefcase,
    Building2,
    MapPin,
    TrendingUp,
    MessageSquare,
    Send,
    BrainCircuit,
    Gavel
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
        permission: 'dashboard.visualizar'
    },
];

/**
 * @description Rotas do cidadão.
 */
const citizenNav = [
    {
        name: 'Fale com o Presidente',
        icon: MessageSquare,
        permission: 'gabinete_virtual.criar', // Permissão para cidadão criar mensagem
        href: route('portalcidadao.gabinete-virtual.index'),
        current: 'portalcidadao.gabinete-virtual.*',
    },
    {
        name: 'Indicação de Projeto',
        icon: Gavel,
        permission: 'sugestoes_de_lei.criar', // Permissão para cidadão criar sugestão
        href: route('portalcidadao.sugestao.create'),
        current: 'portalcidadao.sugestao.*',
    },
];


/**
 * @description Rotas públicas visíveis apenas para o Cidadão logado.
 */
const publicNav = [
    {
        name: 'Acesso Rápido',
        icon: Landmark,
        permission: 'portalcidadao.visualizar', // Permissão para ver o portal
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
        permission: null, // Visibilidade depende dos filhos
        children: [
            { name: 'Solicitações', href: route('admin.solicitacoes.index'), current: 'admin.solicitacoes.*', icon: FileText, permission: 'solicitacoes.visualizar_todos' },
            { name: 'Sugestões de Projetos', href: route('admin.sugestoes.index'), current: 'admin.sugestoes.*', icon: Gavel, permission: 'sugestoes_de_lei.visualizar_todos' },
            { name: 'Funcionários', href: route('admin.funcionarios.index'), current: 'admin.funcionarios.*', icon: Users, permission: 'funcionarios.visualizar_todos' },
            { name: 'Cidadãos', href: route('admin.cidadaos.index'), current: 'admin.cidadaos.*', icon: UserCheck, permission: 'cidadaos.visualizar_todos' },
            { name: 'Serviços', href: route('admin.servicos.index'), current: 'admin.servicos.*', icon: Wrench, permission: 'servicos.visualizar_todos' },
            { name: 'Tipos de Serviço', href: route('admin.tipos-servico.index'), current: 'admin.tipos-servico.*', icon: FolderKanban, permission: 'tipos_servico.visualizar_todos' },
            { name: 'Entidades', href: route('admin.entidades.index'), current: 'admin.entidades.*', icon: Library, permission: 'entidades.visualizar_todos' },
            { name: 'Convênios', href: route('admin.convenios.index'), current: 'admin.convenios.*', icon: Handshake, permission: 'convenios.visualizar_todos' },
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
        permission: null, // Visibilidade depende dos filhos
        children: [
            { name: 'Vagas', href: route('admin.vagas.index'), current: 'admin.vagas.*', icon: FileText, permission: 'vagas_de_emprego.visualizar_todos' },
            { name: 'Empresas', href: route('admin.empresas.index'), current: 'admin.empresas.*', icon: Building2, permission: 'empresas.visualizar_todos' },
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
        permission: null, // Visibilidade depende dos filhos
        children: [
            { name: 'Legislaturas', href: route('admin.legislaturas.index'), current: 'admin.legislaturas.*', icon: CalendarDays, permission: 'memoria_legislativa.visualizar_todos' },
            { name: 'Políticos', href: route('admin.politicos.index'), current: 'admin.politicos.*', icon: UserSquare, permission: 'memoria_legislativa.visualizar_todos' },
            { name: 'Comissões', href: route('admin.comissoes.index'), current: 'admin.comissoes.*', icon: Gavel, permission: 'comissoes.visualizar_todos' },
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
        permission: null, // Visibilidade depende dos filhos
        children: [
            { name: 'Documentos', href: route('admin.achados-e-perdidos-documentos.index'), current: 'admin.achados-e-perdidos-documentos.*', icon: FileBadge, permission: 'achados_e_perdidos.visualizar_todos' },
            { name: 'Pessoas Desaparecidas', href: route('admin.pessoas-desaparecidas.index'), current: 'admin.pessoas-desaparecidas.*', icon: UserSearch, permission: 'pessoas_desaparecidas.visualizar_todos' },
        ]
    }
];

/**
 * @description Menu combinado para Comunicação e Gabinete Virtual.
 */
const comunicacaoGabineteNav = [
    {
        name: 'Comunicação',
        icon: MessageSquare,
        permission: null, // Visibilidade depende dos filhos
        children: [
            {
                name: 'Gabinete Virtual',
                icon: MessageSquare,
                permission: 'gabinete_virtual.visualizar_mensagens',
                href: route('admin.gabinete-virtual.index'),
                current: 'admin.gabinete-virtual.*',
            },
            {
                name: 'Campanhas',
                icon: Send,
                permission: 'campanhas.visualizar_todos',
                href: route('admin.campanhas.index'),
                current: 'admin.campanhas.*',
            },
        ]
    },
];

/**
 * @description Rotas para a seção de Relatórios.
 */
const reportsNav = [
    {
        name: 'Relatórios',
        icon: BarChart3,
        permission: null, // Visibilidade depende dos filhos
        children: [
            { name: 'Atendimentos', href: route('admin.relatorios.atendimentos'), current: 'admin.relatorios.atendimentos', icon: ClipboardCheck, permission: 'relatorios.visualizar_atendimentos' },
            { name: 'Satisfação', href: route('admin.relatorios.satisfacao'), current: 'admin.relatorios.satisfacao', icon: Star, permission: 'relatorios.visualizar_satisfacao' },
            { name: 'Cidadãos', href: route('admin.relatorios.cidadaos'), current: 'admin.relatorios.cidadaos', icon: Users, permission: 'relatorios.visualizar_cidadaos' },
            { name: 'Mapeamento de Demandas', href: route('admin.relatorios.demandas-por-bairro'), current: 'admin.relatorios.demandas-por-bairro', icon: MapPin, permission: 'relatorios.visualizar_demandas_por_bairro' },
            { name: 'Análise de Tendências', href: route('admin.relatorios.analise-de-tendencias'), current: 'admin.relatorios.analise-de-tendencias', icon: TrendingUp, permission: 'relatorios.visualizar_analise_de_tendencias' },
            { name: 'Lideranças por Bairro', href: route('admin.relatorios.mapeamento-politico.index'), current: 'admin.relatorios.mapeamento-politico.index', icon: BrainCircuit, permission: 'relatorios.visualizar_mapeamento_politico' },
        ]
    },
];

/**
 * @description Rotas de configuração do sistema.
 */
const settingsNav = [
    {
        name: 'Configurações',
        icon: SlidersHorizontal,
        permission: null, // Visibilidade depende dos filhos
        children: [
            { name: 'Gerais', href: route('admin.parametros.index'), current: 'admin.parametros.*', icon: Settings, permission: 'configuracoes.visualizar' },
            { name: 'Status de Solicitação', href: route('admin.status-solicitacao.index'), current: 'admin.status-solicitacao.*', icon: ClipboardCheck, permission: 'status_solicitacao.visualizar_todos' },
            { name: 'Campos Personalizados', href: route('admin.custom-fields.index'), current: 'admin.custom-fields.*', icon: ListChecks, permission: 'campos_personalizados.visualizar_todos' },
            { name: 'Papéis', href: route('admin.roles-permissions.index'), current: 'admin.roles-permissions.*', icon: ShieldCheck, permission: 'perfis.visualizar_todos' },
            { name: 'Permissões', href: route('admin.permissions.index'), current: 'admin.permissions.*', icon: KeyRound, permission: 'perfis.visualizar_todos' },
            { name: 'Auditoria', href: route('admin.auditoria.index'), current: 'admin.auditoria.index', icon: History, permission: 'logs_de_atividade.visualizar_todos' },
        ]
    },
];

// Montagem final do menu de navegação
export const navigation = [
    ...coreNav,
    ...citizenNav,
    ...publicNav,
    ...managementNav,
    ...jobsNav,
    ...memoriaLegislativaNav,
    ...lostAndFoundNav,
    ...comunicacaoGabineteNav,
    ...reportsNav,
    ...settingsNav,
];
