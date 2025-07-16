<?php

namespace App\Providers;

// Imports do seu arquivo original
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

// --- NOSSAS ADIÇÕES ---
use App\Actions\Fortify\Tenant\AttemptToAuthenticate; // Adicionado para o login do tenant
use App\Actions\Fortify\Tenant\ResetUserPassword;
use App\Auth\Passwords\CustomDatabaseTokenRepository;
use App\Models\Central\Tenant;
use Illuminate\Auth\Passwords\PasswordBroker;
use Laravel\Fortify\Contracts\AttemptToAuthenticate as AttemptToAuthenticateContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Adiciona o binding para a ação de autenticação customizada do tenant.
        // Isso garante que, quando o Fortify tentar autenticar, ele usará nossa lógica
        // que força o uso do 'tenant' guard.
        $this->app->singleton(
            AttemptToAuthenticateContract::class,
            AttemptToAuthenticate::class
        );

        // Sobrescreve como o 'PasswordBroker' é criado no container de serviços.
        $this->app->singleton('auth.password.broker', function ($app) {
            // Se estivermos em um contexto de tenant...
            if (Tenant::checkCurrent()) {
                Log::info("[DEBUG] FortifyServiceProvider: Criando PasswordBroker CUSTOMIZADO para TENANT.");

                $configKey = 'auth.passwords.tenant_users';
                if (! $app['config']->has($configKey)) {
                    Log::error("[DEBUG] Configuração de senha para tenants não encontrada: {$configKey}");
                    throw new \RuntimeException("Configuração de senha para tenants ausente.");
                }
                $config = $app['config'][$configKey];

                // Usa nosso repositório de tokens customizado para podermos logar
                $tokens = new CustomDatabaseTokenRepository(
                    DB::connection($config['connection']), // Usa a conexão 'tenant'
                    $app['hash'],
                    $config['table'],
                    $app['config']['app.key'],
                    $config['expire'],
                    $config['throttle'] ?? 0
                );

                $users = $app['auth']->createUserProvider($config['provider']);

                // Retorna um PasswordBroker totalmente novo e configurado corretamente
                return new PasswordBroker($tokens, $users);
            }

            // ... caso contrário, apenas retorna o broker padrão do Laravel para o landlord.
            Log::info("[DEBUG] FortifyServiceProvider: Usando PasswordBroker padrão do LANDLORD.");
            return $app['auth.password']->broker('users'); // Especifica 'users' para clareza
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mantém suas configurações existentes
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Mantém seus Rate Limiters
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
