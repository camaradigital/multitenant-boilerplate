<?php

namespace App\Providers;

// Imports para a lógica de autenticação multi-tenant
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\Tenant\AttemptToAuthenticate;
use App\Actions\Fortify\Tenant\ResetUserPassword as TenantResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
// Imports para as customizações de Login e Logout
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\CustomLogoutResponse;
use App\Http\Responses\TenantLoginResponse;
use App\Http\Responses\TenantRegisterResponse;
use App\Models\Tenant\User as TenantUser;
use App\Models\User as CentralUser;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
// Imports Padrão e outras Ações Customizadas do Fortify
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\AttemptToAuthenticate as AttemptToAuthenticateContract;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
// --- ADICIONADO ---
// Importe a classe Inertia para renderizar a view com dados.
use Laravel\Fortify\Fortify;
// Importe a classe Carbon para manipulação de datas.
use Spatie\Multitenancy\Models\Tenant;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            AttemptToAuthenticateContract::class,
            AttemptToAuthenticate::class
        );

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
        Fortify::authenticateUsing(function (Request $request) {
            if ($tenant = Tenant::current()) {
                $user = TenantUser::where('email', $request->email)->first();
                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            } else {
                $user = CentralUser::where('email', $request->email)->first();
                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            }

            return null;
        });

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
                        $freshTenant->endereco_estado,
                    ]);
                    $address = implode(', ', $addressParts);
                    $date = Carbon::now()->locale('pt_BR')->translatedFormat('d \\de F \\de Y');

                    // Mapeia os placeholders para os valores reais do tenant
                    $replacements = [
                        '[Nome da Câmara Municipal]' => $freshTenant->name,
                        '[CNPJ da Câmara]' => $freshTenant->cnpj,
                        '[Endereço Completo da Câmara]' => $address,
                        '[Cidade]' => $freshTenant->endereco_cidade,
                        '[dia] de [mês] de [ano]' => $date,
                        // O placeholder [Nome Completo do Cidadão] agora é ignorado aqui
                    ];

                    // Substitui os placeholders nos textos
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

        // --- ROTAS DE VISUALIZAÇÃO REMOVIDAS ---
        // As rotas de redefinição de senha e verificação de e-mail agora são
        // definidas diretamente em routes/tenant.php para garantir que
        // elas operem dentro do contexto do middleware do tenant.
        //
        // Fortify::requestPasswordResetLinkView(fn () => inertia('Auth/ForgotPassword'));
        // Fortify::resetPasswordView(fn (Request $request) => inertia('Auth/ResetPassword', ['email' => $request->email, 'token' => $request->route('token')]));
        // Fortify::verifyEmailView(fn () => inertia('Auth/VerifyEmail'));

        Fortify::twoFactorChallengeView(fn () => inertia('Auth/TwoFactorChallenge'));
        Fortify::confirmPasswordView(fn () => inertia('Auth/ConfirmPassword'));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
