<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection; // Importar o trait do Spatie
use Spatie\Permission\Traits\HasRoles; // Se você usa Spatie Permission para usuários do tenant

class User extends Authenticatable
{
    use HasFactory, Notifiable, UsesTenantConnection, HasRoles; // Adicionar UsesTenantConnection e HasRoles

    // Define explicitamente a conexão para este modelo.
    // O UsesTenantConnection já faz isso, mas ter a propriedade 'protected $connection'
    // pode ser útil para clareza e em alguns cenários específicos.
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the e-mail address where password reset links are sent.
     * Necessário para a funcionalidade de redefinição de senha.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    // Adicione relacionamentos e outros métodos específicos do usuário do tenant aqui
}

