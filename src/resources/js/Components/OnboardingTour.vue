<script setup>
import { onMounted, onUnmounted, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

// Acessa as propriedades da página, incluindo os dados do usuário autenticado
const page = usePage();

// Verifica de forma reativa se o usuário logado possui a role 'Admin Tenant'
const isAdminTenant = computed(() => {
    // Garante que o caminho existe para evitar erros e retorna um array vazio se não existir
    const roles = page.props.auth.user?.roles || [];
    return roles.includes('Admin Tenant');
});


let tourInstance = null;

const createAndStartTour = () => {
    // A LINHA CORRIGIDA: Limpa o status antes de iniciar um novo tour.
    localStorage.removeItem('onboarding_tour_completed');

    if (tourInstance && tourInstance.isActive()) {
        tourInstance.cancel();
    }

    const navigateAndNext = (tour, routeName) => {
        if (typeof route === 'function' && route().has(routeName)) {
            router.visit(route(routeName), {
                onSuccess: () => setTimeout(() => tour.next(), 300),
            });
        } else {
            console.error(`[OnboardingTour] A rota "${routeName}" não foi encontrada.`);
            setTimeout(() => tour.next(), 300);
        }
    };

    const tour = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            classes: 'shepherd-tour-modern', // Nova classe principal
            scrollTo: { behavior: 'smooth', block: 'center' },
            cancelIcon: { enabled: true, label: 'Fechar tour' },
        }
    });

    // --- Passos do Tour Corrigidos e Atualizados ---
    tour.addStep({
        id: 'welcome',
        title: '👋 Bem-vindo(a) à Câmara Digital!',
        text: 'Este guia rápido irá apresentar as principais funcionalidades do Painel Administrativo. Vamos começar!',
        attachTo: { element: '.main-header', on: 'bottom' },
        buttons: [
            { text: 'Pular', classes: 'shepherd-button-secondary', action: tour.complete },
            { text: 'Começar →', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'dashboard',
        title: 'Dashboard Administrativo',
        text: 'Bem-vindo ao painel de controle! Aqui você tem uma visão geral das principais métricas e atividades do sistema.',
        attachTo: { element: '#admin-dashboard-link', on: 'bottom' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: tour.next }
        ]
    });

    // PASSO CORRIGIDO: Este passo clica no menu pai para garantir que "Parâmetros" esteja visível
    tour.addStep({
        id: 'open-configuracoes-menu',
        title: 'Configurações Iniciais',
        text: 'Vamos começar pelas configurações. Esta área permite ajustar os parâmetros gerais do sistema.',
        attachTo: { element: '#configuracoes-menu', on: 'right' }, // Garanta que o botão do menu tenha este ID
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            {
                text: 'Avançar →',
                action: function() {
                    const menuTrigger = document.querySelector('#configuracoes-menu');
                    if (menuTrigger) {
                        // Simula o clique para abrir o submenu antes de ir para o próximo passo
                        menuTrigger.click();
                    }
                    // Adiciona uma pequena pausa para o Vue renderizar os sub-itens
                    setTimeout(() => this.next(), 200);
                }
            }
        ]
    });

    // Este passo agora vai encontrar o link "Parâmetros" sem problemas
    tour.addStep({
        id: 'parametros-link',
        title: 'Parâmetros do Sistema',
        text: 'Ajuste os parâmetros gerais, defina a visibilidade dos módulos e gerencie permissões de acesso.',
        attachTo: { element: 'a[href*="/parametros"]', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Ir para Parâmetros →', action: () => navigateAndNext(tour, 'admin.parametros.index') }
        ]
    });

    tour.addStep({
        id: 'atendimento',
        title: 'Módulo de Atendimento',
        text: 'Gerencie todas as solicitações de serviços dos cidadãos, anexe documentos, altere status e acompanhe o andamento de cada pedido.',
        attachTo: { element: '#atendimento-menu', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.solicitacoes.index') }
        ]
    });

    tour.addStep({
        id: 'utilizadores',
        title: 'Gestão de Utilizadores',
        text: 'Administre os cadastros de funcionários e cidadãos, gerencie permissões e acesse os dados dos usuários do sistema.',
        attachTo: { element: '#utilizadores-menu', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.funcionarios.index') }
        ]
    });

    tour.addStep({
        id: 'servicos',
        title: 'Gestão de Serviços',
        text: 'Crie, edite e organize os serviços e tipos de serviços oferecidos pela câmara aos cidadãos.',
        attachTo: { element: '#servicos-menu', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.servicos.index') }
        ]
    });

    tour.addStep({
        id: 'achados-e-perdidos',
        title: 'Achados e Perdidos',
        text: 'Gerencie os registros de documentos encontrados e pessoas desaparecidas para exibição no portal público.',
        attachTo: { element: '#achados-e-perdidos-menu', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.achados-e-perdidos-documentos.index') }
        ]
    });

    tour.addStep({
        id: 'vagas',
        title: 'Vagas de Emprego',
        text: 'Publique e gerencie vagas de emprego. Aqui você também pode cadastrar as empresas parceiras e visualizar as candidaturas recebidas.',
        attachTo: { element: '#vagas-menu', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.vagas.index') }
        ]
    });

    tour.addStep({
        id: 'entidades-convenios',
        title: 'Entidades e Convênios',
        text: 'Cadastre e administre as entidades e convênios parceiros da câmara.',
        attachTo: { element: '#entidades-convenios-menu', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.entidades.index') }
        ]
    });

    tour.addStep({
        id: 'memoria-legislativa',
        title: 'Memória Legislativa',
        text: 'Administre o conteúdo histórico do legislativo, incluindo o cadastro de políticos, legislaturas e mandatos.',
        attachTo: { element: '#memoria-legislativa-menu', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.politicos.index') }
        ]
    });

    tour.addStep({
        id: 'relatorios',
        title: 'Módulo de Relatórios',
        text: 'Acesse relatórios detalhados sobre atendimentos, cidadãos cadastrados e pesquisas de satisfação para auxiliar na tomada de decisões.',
        attachTo: { element: '#relatorios-menu', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.relatorios.atendimentos') }
        ]
    });

    tour.addStep({
        id: 'auditoria',
        title: 'Logs de Auditoria',
        text: 'Acompanhe todas as ações importantes realizadas no sistema para garantir a segurança e o controle das operações.',
        attachTo: { element: '#auditoria-link', on: 'right' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.auditoria.index') }
        ]
    });

    tour.addStep({
        id: 'finish',
        title: '✅ Tour Concluído!',
        text: 'Você explorou as principais áreas do sistema. Agora você está pronto para gerenciar a Câmara Digital!',
        attachTo: { element: '.user-menu', on: 'bottom' },
        buttons: [
            { text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back },
            { text: 'Finalizar', action: tour.complete }
        ]
    });

    tourInstance = tour;

    const onTourEnd = () => {
        localStorage.setItem('onboarding_tour_completed', 'true');
        if (typeof route === 'function' && route().has('tenant.dashboard')) {
            router.visit(route('tenant.dashboard'));
        } else {
            console.error(`[OnboardingTour] A rota "tenant.dashboard" não foi encontrada para finalizar o tour.`);
        }
    };

    tour.on('complete', onTourEnd);
    tour.on('cancel', onTourEnd);

    tour.start();
};

onMounted(() => {
    // A CONDIÇÃO ATUALIZADA:
    // Verifica se o usuário é 'Admin Tenant' E se o tour ainda não foi completado.
    if (isAdminTenant.value && localStorage.getItem('onboarding_tour_completed') !== 'true') {
        // Adiciona um pequeno atraso para garantir que o DOM e o Vue estejam prontos
        setTimeout(createAndStartTour, 500);
    }
    // O evento para reiniciar o tour continua funcionando para qualquer usuário que tenha o botão
    window.addEventListener('restart-onboarding-tour', createAndStartTour);
});

onUnmounted(() => {
    window.removeEventListener('restart-onboarding-tour', createAndStartTour);
    if (tourInstance && tourInstance.isActive()) {
        tourInstance.cancel();
    }
});
</script>

<template>
  <div />
</template>

<style>
/* ==========================================================================
   Estilização Moderna e Profissional para o Shepherd.js Tour
   ========================================================================== */

/* Efeito de vidro para o overlay */
.shepherd-modal-overlay-container {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    background-color: rgba(0, 0, 0, 0.3);
}

/* Animação de entrada */
.shepherd-element {
    animation: fadeInScale 0.3s ease-out forwards;
    transform-origin: center;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Container Principal do Tour */
.shepherd-tour-modern {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    max-width: 420px;
    border: 1px solid #f0f0f0;
    transition: all 0.2s ease-in-out;
}

.dark .shepherd-tour-modern {
    background-color: #182235; /* Um azul escuro mais rico */
    border-color: #2a3a5a;
}

/* Cabeçalho */
.shepherd-tour-modern .shepherd-header {
    background-color: #f7faff;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #eef2f9;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dark .shepherd-tour-modern .shepherd-header {
    background-color: #1f2c45;
    border-bottom-color: #2a3a5a;
}

/* Título */
.shepherd-tour-modern .shepherd-title {
    color: #1e293b;
    font-size: 1.125rem; /* 18px */
    font-weight: 600;
}

.dark .shepherd-tour-modern .shepherd-title {
    color: #e2e8f0;
}

/* Ícone de Fechar */
.shepherd-tour-modern .shepherd-cancel-icon {
    color: #94a3b8;
    transition: color 0.2s ease;
}

.shepherd-tour-modern .shepherd-cancel-icon:hover {
    color: #334155;
}

.dark .shepherd-tour-modern .shepherd-cancel-icon:hover {
    color: #ffffff;
}

/* Conteúdo de Texto */
.shepherd-tour-modern .shepherd-text {
    padding: 1.5rem;
    color: #475569;
    font-size: 1rem; /* 16px */
    line-height: 1.7;
}

.dark .shepherd-tour-modern .shepherd-text {
    color: #94a3b8;
}

/* Rodapé */
.shepherd-tour-modern .shepherd-footer {
    padding: 0 1.5rem 1.25rem;
    display: flex;
    justify-content: flex-end; /* Alinha os botões à direita */
    gap: 0.75rem; /* Espaço entre os botões */
    border-top: 1px solid #eef2f9;
    padding-top: 1.25rem;
}

.dark .shepherd-tour-modern .shepherd-footer {
    border-top-color: #2a3a5a;
}

/* Botões (Estilo Base) */
.shepherd-tour-modern .shepherd-button {
    border: none;
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem; /* 14px */
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
/* Efeito de foco para acessibilidade */
.shepherd-tour-modern .shepherd-button:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb), 0.4);
}

/* Botão Primário */
.shepherd-tour-modern .shepherd-button:not(.shepherd-button-secondary) {
    background-color: var(--color-primary, #059669);
    color: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
}

.shepherd-tour-modern .shepherd-button:not(.shepherd-button-secondary):hover {
    filter: brightness(1.1);
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
}

/* Botão Secundário */
.shepherd-tour-modern .shepherd-button-secondary {
    background-color: #f1f5f9;
    color: #475569;
}

.dark .shepherd-tour-modern .shepherd-button-secondary {
    background-color: #334155;
    color: #cbd5e1;
}

.shepherd-tour-modern .shepherd-button-secondary:hover {
    background-color: #e2e8f0;
    color: #1e293b;
}

.dark .shepherd-tour-modern .shepherd-button-secondary:hover {
    background-color: #475569;
    color: #f1f5f9;
}

/* Seta Indicadora */
.shepherd-arrow::before {
    background-color: #ffffff !important;
}

.dark .shepherd-arrow::before {
    background-color: #182235 !important;
}
</style>
