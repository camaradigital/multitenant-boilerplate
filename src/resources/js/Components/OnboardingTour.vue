<script setup>
import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

let tourInstance = null;

const createAndStartTour = () => {
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
            classes: 'shepherd-custom',
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
  if (localStorage.getItem('onboarding_tour_completed') !== 'true') {
    // Adiciona um pequeno atraso para garantir que o DOM e o Vue estejam prontos
    setTimeout(createAndStartTour, 500);
  }
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
/* Estilização Customizada para o Shepherd.js */
.shepherd-custom {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    max-width: 400px;
    border: 1px solid #e2e8f0;
}

.dark .shepherd-custom {
    background-color: #1f2937; /* Cor de fundo para o modo escuro */
    border: 1px solid #374151;
}

.shepherd-custom .shepherd-header {
    background-color: #f8fafc;
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.dark .shepherd-custom .shepherd-header {
    background-color: #374151; /* Cor do header para o modo escuro */
    border-bottom: 1px solid #4b5563;
}

.shepherd-custom .shepherd-title {
    color: #111827;
    font-size: 1.125rem;
    font-weight: 700;
}

.dark .shepherd-custom .shepherd-title {
    color: #f9fafb; /* Cor do título para o modo escuro */
}

.shepherd-custom .shepherd-cancel-icon {
    color: #9ca3af;
    transition: color 0.2s ease-in-out;
}

.shepherd-custom .shepherd-cancel-icon:hover {
    color: #111827;
}

.dark .shepherd-custom .shepherd-cancel-icon:hover {
    color: #ffffff;
}

.shepherd-custom .shepherd-text {
    padding: 1rem;
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.6;
}

.dark .shepherd-custom .shepherd-text {
    color: #d1d5db; /* Cor do texto para o modo escuro */
}

.shepherd-custom .shepherd-footer {
    padding: 0 1rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.shepherd-custom .shepherd-button {
    background-color: var(--color-primary, #4F46E5); /* Usa a variável de cor primária do seu sistema */
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    font-weight: 600;
    transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
    border: none;
    cursor: pointer;
}

.shepherd-custom .shepherd-button:hover {
    filter: brightness(110%);
    transform: translateY(-1px);
}

.shepherd-custom .shepherd-button-secondary {
    background-color: transparent;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.dark .shepherd-custom .shepherd-button-secondary {
    color: #9ca3af;
    border-color: #4b5563;
}

.shepherd-custom .shepherd-button-secondary:hover {
    background-color: #f3f4f6;
    color: #1f2937;
}

.dark .shepherd-custom .shepherd-button-secondary:hover {
    background-color: #374151;
    color: #f9fafb;
}

.shepherd-arrow::before {
    background-color: #ffffff !important;
}

.dark .shepherd-arrow::before {
    background-color: #1f2937 !important; /* Cor da seta para o modo escuro */
}
</style>
