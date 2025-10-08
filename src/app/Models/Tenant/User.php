<?php

namespace App\Models\Tenant;

use App\Notifications\Tenant\VerifyTenantEmail;
use App\Notifications\TenantResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
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

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasProfilePhoto, HasRoles, HasTeams, LogsActivity, Notifiable, TwoFactorAuthenticatable, UsesTenantConnection;

    protected $guard_name = 'tenant';

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'is_active',
        'profile_data',
        'bairro_id',
        'terms_accepted_at',
        'privacy_accepted_at',
        'engagement_score',
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
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    /**
     * Accessor: Garante que os dados sejam descriptografados e convertidos para array ao serem lidos.
     */
    public function getProfileDataAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($value);

            return json_decode($decrypted, true) ?? []; // Garante que retorne um array mesmo se o JSON for inválido
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error("Falha ao descriptografar profile_data para o usuário ID: {$this->id}. Erro: ".$e->getMessage());

            return []; // Retorna um array vazio em caso de falha
        }
    }

    /**
     * Mutator: Garante que o array seja convertido para JSON e criptografado ao ser salvo.
     */
    public function setProfileDataAttribute($value): void
    {
        if (is_array($value)) {
            $jsonValue = json_encode($value);
            $this->attributes['profile_data'] = Crypt::encryptString($jsonValue);
        } else {
            $this->attributes['profile_data'] = null;
        }
    }

    /**
     * ADICIONADO: Relação com o modelo Bairro.
     * O usuário pertence a um bairro.
     */
    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class);
    }

    /**
     * Configura como as atividades deste modelo devem ser logadas.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $logAttributes = array_merge($this->fillable, ['roles']);

        return LogOptions::defaults()
            ->logOnly($logAttributes)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
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

        $domain = $tenant->subdomain.'.'.config('app.central_domain');
        $scheme = config('app.env') === 'production' ? 'https' : 'http';

        $url = $scheme.'://'.$domain.route('password.reset', [
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
        return $this->hasMany(\App\Models\Tenant\SolicitacaoServico::class, 'user_id');
    }

    /**
     * Solicitações onde este usuário é o Funcionário (quem atendeu).
     */
    public function solicitacoesAtendidas(): HasMany
    {
        return $this->hasMany(\App\Models\Tenant\SolicitacaoServico::class, 'atendente_id');
    }

    /**
     * Pesquisas de satisfação respondidas por este usuário.
     */
    public function pesquisas_satisfacao(): HasMany
    {
        return $this->hasMany(\App\Models\Tenant\PesquisaSatisfacao::class, 'user_id', 'id');
    }

    /**
     * Candidaturas a vagas de emprego feitas por este usuário. (ADICIONADO)
     */
    public function candidaturas(): HasMany
    {
        return $this->hasMany(Candidatura::class, 'user_id');
    }

    /**
     * Os tipos de serviço que o funcionário está autorizado a atender.
     */
    public function tiposDeServicoAtendidos(): BelongsToMany
    {
        return $this->belongsToMany(TipoServico::class, 'funcionario_tipo_servico', 'user_id', 'tipo_servico_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(NotaCidadao::class, 'user_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'user_tags');
    }

    public function gabineteMessages(): HasMany
    {
        return $this->hasMany(GabineteVirtualMensagem::class, 'user_id');
    }
}
