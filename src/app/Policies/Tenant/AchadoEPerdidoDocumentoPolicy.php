<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\AchadoEPerdidoDocumento;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AchadoEPerdidoDocumentoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('achados_e_perdidos.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return bool
     */
    public function view(User $user, AchadoEPerdidoDocumento $achadosEPerdidosDocumento)
    {
        return $user->can('achados_e_perdidos.visualizar');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('achados_e_perdidos.criar');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return bool
     */
    public function update(User $user, AchadoEPerdidoDocumento $achadosEPerdidosDocumento)
    {
        return $user->can('achados_e_perdidos.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return bool
     */
    public function delete(User $user, AchadoEPerdidoDocumento $achadosEPerdidosDocumento)
    {
        return $user->can('achados_e_perdidos.excluir');
    }
}
