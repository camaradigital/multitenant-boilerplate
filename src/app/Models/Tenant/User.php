<?php

namespace App\Models\Tenant;

// use App\Casts\EncryptedCast; // REMOVIDO - Não é mais necessário
use App\Notifications\Tenant\VerifyTenantEmail;
use App\Notifications\TenantResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt; // Adicionado para o Accessor/Mutator
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
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, HasRoles, UsesTenantConnection, LogsActivity, HasTeams;

    protected $guard_name = 'tenant';

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'is_active',
        'profile_data',
        'bairro',
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
            // 'profile_data' => EncryptedCast::class, // REMOVIDO daqui para usar Accessor/Mutator
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    // --- SOLUÇÃO FINAL: Accessor e Mutator para controle total ---

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
            Log::error("Falha ao descriptografar profile_data para o usuário ID: {$this->id}. Erro: " . $e->getMessage());
            return []; // Retorna um array vazio em caso de falha
        }
    }

    /**
     * Mutator: Garante que o array seja convertido para JSON e criptografado ao ser salvo.
     */
    public function setProfileDataAttribute($value): void
    {
        if (is_array($value)) {
            // --- CORREÇÃO APLICADA AQUI ---
            // Alinhado para buscar 'endereco_bairro' dentro do array, que é o nome vindo do formulário.
            $this->attributes['bairro'] = $value['endereco_bairro'] ?? null;

            // Continua com a criptografia normal para o profile_data
            $jsonValue = json_encode($value);
            $this->attributes['profile_data'] = Crypt::encryptString($jsonValue);
        } else {
            // Garante que, se profile_data for nulo, a coluna bairro também seja.
            $this->attributes['bairro'] = null;
            $this->attributes['profile_data'] = null;
        }
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
        if (!$tenant = Tenant::current()) {
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
}
