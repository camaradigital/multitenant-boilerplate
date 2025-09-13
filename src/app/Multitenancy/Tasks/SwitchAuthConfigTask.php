<?php

namespace App\Multitenancy\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchAuthConfigTask implements SwitchTenantTask
{
    /**
     * @var string|null Armazena o broker original do Fortify.
     */
    protected ?string $originalFortifyBroker;

    /**
     * @var string|null Armazena o broker padrão original do Auth.
     */
    protected ?string $originalAuthBroker;

    /**
     * @var string|null Armazena o guard original do Fortify.
     */
    protected ?string $originalFortifyGuard;

    /**
     * Guarda as configurações originais do landlord no momento da construção.
     */
    public function __construct()
    {
        $this->originalFortifyBroker = config('fortify.passwords');
        $this->originalAuthBroker = config('auth.defaults.passwords');
        $this->originalFortifyGuard = config('fortify.guard');
    }

    /**
     * Executado quando um tenant se torna o atual.
     * Altera a configuração do Fortify E a configuração padrão do Auth.
     */
    public function makeCurrent(IsTenant $tenant): void
    {
        config()->set('fortify.passwords', 'tenant_users');
        config()->set('auth.defaults.passwords', 'tenant_users');
        config()->set('fortify.guard', 'tenant');
    }

    /**
     * Executado quando o contexto do tenant é finalizado.
     * Restaura ambas as configurações originais do landlord.
     */
    public function forgetCurrent(): void
    {
        config()->set('fortify.passwords', $this->originalFortifyBroker);
        config()->set('auth.defaults.passwords', $this->originalAuthBroker);
        config()->set('fortify.guard', $this->originalFortifyGuard);
    }
}
