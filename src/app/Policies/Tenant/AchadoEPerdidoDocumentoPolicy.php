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
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Qualquer usuário autenticado pode ver a lista de documentos.
        // Se você tiver uma permissão específica para isso, pode adicioná-la aqui.
        // Ex: return $user->can('view_any_achados_e_perdidos_documentos');
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, AchadoEPerdidoDocumento $achadosEPerdidosDocumento)
    {
        // Qualquer usuário autenticado pode ver os detalhes de um documento.
        // Ex: return $user->can('view_achados_e_perdidos_documentos');
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('gerenciar achados e perdidos');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, AchadoEPerdidoDocumento $achadosEPerdidosDocumento)
    {
        return $user->can('gerenciar achados e perdidos');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, AchadoEPerdidoDocumento $achadosEPerdidosDocumento)
    {
        return $user->can('gerenciar achados e perdidos');
    }
}
