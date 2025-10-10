<!DOCTYPE html>
<html lang="pt-BR" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado</title>

    {{-- Importando a fonte Inter, usada no seu dashboard --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Usando o Tailwind CSS via CDN para simplicidade --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        // Este script aplica o tema escuro/claro com base na preferência do sistema operacional
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        /* Aplicando a fonte Inter como padrão */
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">

    <div class="flex flex-col items-center justify-center min-h-screen px-4 text-center">

        {{-- O card principal, com espaçamento e layout aprimorados --}}
        <div class="max-w-md w-full p-8 space-y-8 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">

            {{-- Ícone de erro (rose) --}}
            <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center bg-rose-100 dark:bg-rose-900/50">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600 dark:text-rose-400">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                    <path d="m2 2 20 20"/>
                    <path d="M5 5 8 8"/>
                </svg>
            </div>

            {{-- Textos principais --}}
            <div class="space-y-2">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                    Acesso Negado
                </h1>
                <p class="text-base text-gray-500 dark:text-gray-400">
                    Desculpe, você não tem permissão para acessar esta página.
                </p>
            </div>

            {{-- N O V O   B L O C O   D E   M E N S A G E M --}}
            {{-- Condicional para exibir a mensagem de exceção customizada --}}
            @if(config('app.debug') && !empty($exception->getMessage()) && $exception->getMessage() !== 'This action is unauthorized.')
                <div class="flex items-start text-left space-x-3 bg-emerald-50 dark:bg-emerald-900/50 p-4 rounded-lg">
                    {{-- Ícone de informação --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    {{-- Mensagem com a cor solicitada --}}
                    <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">
                        {{ $exception->getMessage() }}
                    </p>
                </div>
            @endif

            {{-- Botão de ação --}}
            <a href="{{ url('/dashboard') }}"
               class="inline-flex items-center justify-center px-6 py-2.5 bg-emerald-600 text-white font-semibold text-sm rounded-lg shadow-md hover:bg-emerald-700 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                    <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                </svg>
                Voltar para o Dashboard
            </a>
        </div>

    </div>

</body>
</html>
