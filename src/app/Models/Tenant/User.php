<?php

namespace App\Models\Tenant;

use App\Casts\EncryptedCast;
use App\Notifications\Tenant\VerifyTenantEmail; // 1. IMPORTAÇÃO ADICIONADA
use App\Notifications\TenantResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail; // 2. IMPORTAÇÃO NECESSÁRIA
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Permission\Traits\HasRoles;

// 3. IMPLEMENTAÇÃO DO CONTRATO
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, HasRoles, UsesTenantConnection, LogsActivity, HasTeams;

    protected $guard_name = 'tenant';

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'is_active',
        'profile_data',
        'terms_accepted_at',
        'privacy_accepted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'profile_data' => EncryptedCast::class,
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    /**
     * Configura como as atividades deste modelo devem ser logadas.
     */
    public function getActivitylogOptions(): LogOptions
    {
        // CORREÇÃO: Combinamos os campos do $fillable com a relação 'roles'
        // para garantir que ambos sejam auditados.
        $logAttributes = array_merge($this->fillable, ['roles']);

        return LogOptions::defaults()
            ->logOnly($logAttributes)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $events = ['created' => 'criado', 'updated' => 'atualizado', 'deleted' => 'excluído'];
                return "O usuário {$this->name} foi {$events[$eventName]}.";
            });
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    public function sendPasswordResetNotification($token)
    {
        if (! $tenant = Tenant::current()) {
            Log::warning('Tentativa de enviar reset de senha sem um tenant atual.');
            return;
        }

        $domain = $tenant->subdomain . '.' . config('app.central_domain');
        $scheme = config('app.env') === 'production' ? 'https' : 'http';

        $url = $scheme . "://" . $domain . route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false);

        $this->notify(new TenantResetPasswordNotification($token, $url));
    }

    /**
     * Envia a notificação de verificação de e-mail.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyTenantEmail);
    }

    /**
     * Solicitações onde este usuário é o Cidadão (quem pediu).
     */
    public function solicitacoes(): HasMany
    {
        // O modelo correto para Solicitação de Serviço deve ser referenciado aqui.
        // Se o namespace estiver incorreto, ajuste conforme necessário.
        return $this->hasMany(\App\Models\Tenant\SolicitacaoServico::class, 'user_id');
    }

    /**
     * Solicitações onde este usuário é o Funcionário (quem atendeu).
     */
    public function solicitacoesAtendidas(): HasMany
    {
        // O modelo correto para Solicitação de Serviço deve ser referenciado aqui.
        return $this->hasMany(\App\Models\Tenant\SolicitacaoServico::class, 'atendente_id');
    }

    /**
     * Pesquisas de satisfação respondidas por este usuário.
     */
    public function pesquisas_satisfacao(): HasMany
    {
        // O modelo correto para Pesquisa de Satisfação deve ser referenciado aqui.
        return $this->hasMany(\App\Models\Tenant\PesquisaSatisfacao::class, 'user_id', 'id');
    }
}
