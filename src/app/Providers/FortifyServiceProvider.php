<?php

namespace App\Providers;

use Illuminate\Validation\ValidationException;

// Imports para a lógica de autenticação multi-tenant
use App\Models\User as CentralUser;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant;

// Imports para as customizações de Login e Logout
use App\Http\Responses\CustomLogoutResponse;
use App\Http\Responses\TenantLoginResponse;
use App\Http\Responses\TenantRegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

// Imports Padrão e outras Ações Customizadas do Fortify
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Fortify\Tenant\ResetUserPassword as TenantResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

// Importe a classe Inertia para renderizar a view com dados.
use Inertia\Inertia;
// Importe a classe Carbon para manipulação de datas.
use Carbon\Carbon;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // A vinculação do AttemptToAuthenticateContract foi removida,
        // pois a lógica agora está centralizada no método boot().
        $this->app->singleton(
            \Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse::class,
            \App\Http\Responses\ProfileUpdateResponse::class
        );

        $this->app->singleton(
            LoginResponseContract::class,
            TenantLoginResponse::class
        );

        $this->app->singleton(
            LogoutResponseContract::class,
            CustomLogoutResponse::class
        );

        $this->app->singleton(
            RegisterResponseContract::class,
            TenantRegisterResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- LÓGICA DE AUTENTICAÇÃO CENTRALIZADA E CORRIGIDA ---
        Fortify::authenticateUsing(function (Request $request) {
            $isTenantContext = Tenant::current();
            $model = $isTenantContext ? TenantUser::class : CentralUser::class;

            $user = $model::where('email', $request->email)->first();

            // Valida se o usuário existe e a senha está correta.
            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    // Usa a tradução padrão do Laravel para falha de autenticação.
                    'email' => [__('auth.failed')],
                ]);
            }

            // Valida se o e-mail foi verificado (apenas para tenants).
            if ($isTenantContext && ! $user->hasVerifiedEmail()) {
                throw ValidationException::withMessages([
                    'email' => ['Sua conta de e-mail precisa ser verificada antes de você poder fazer o login.'],
                ]);
            }

            return $user;
        });
        // --- FIM DA LÓGICA DE AUTENTICAÇÃO ---

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(TenantResetUserPassword::class);

        Fortify::loginView(fn () => inertia('Auth/Login'));

        Fortify::registerView(function () {
            $currentTenant = Tenant::current();
            $processedTenantData = null;

            if ($currentTenant) {
                $freshTenant = Tenant::find($currentTenant->id);
                if ($freshTenant) {
                    $addressParts = array_filter([
                        $freshTenant->endereco_logradouro,
                        $freshTenant->endereco_numero,
                        $freshTenant->endereco_bairro,
                        $freshTenant->endereco_cidade,
                        $freshTenant->endereco_estado
                    ]);
                    $address = implode(', ', $addressParts);
                    $date = Carbon::now()->locale('pt_BR')->translatedFormat('d \\de F \\de Y');

                    $replacements = [
                        '[Nome da Câmara Municipal]'   => $freshTenant->name,
                        '[CNPJ da Câmara]'             => $freshTenant->cnpj,
                        '[Endereço Completo da Câmara]' => $address,
                        '[Cidade]'                     => $freshTenant->endereco_cidade,
                        '[dia] de [mês] de [ano]'      => $date,
                    ];

                    $processedTerms = str_replace(array_keys($replacements), array_values($replacements), $freshTenant->terms_of_service ?? '');
                    $processedPolicy = str_replace(array_keys($replacements), array_values($replacements), $freshTenant->privacy_policy ?? '');

                    $processedTenantData = [
                        'name' => $freshTenant->name,
                        'terms_of_service' => $processedTerms,
                        'privacy_policy' => $processedPolicy,
                    ];
                }
            }
            return Inertia::render('Auth/Register', [
                'tenant' => $processedTenantData,
            ]);
        });

        Fortify::twoFactorChallengeView(fn () => inertia('Auth/TwoFactorChallenge'));
        Fortify::confirmPasswordView(fn () => inertia('Auth/ConfirmPassword'));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}

