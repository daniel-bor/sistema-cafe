<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilamentResourcePolicy
{
    use HandlesAuthorization;

    /**
     * Verificar si el usuario puede ver cualquier recurso del panel agricultor
     */
    public function viewAnyAgricultorResource(User $user): bool
    {
        return $user->canAccessAgricultorPanel();
    }

    /**
     * Verificar si el usuario puede ver cualquier recurso del panel beneficio
     */
    public function viewAnyBeneficioResource(User $user): bool
    {
        return $user->canAccessBeneficioPanel();
    }

    /**
     * Verificar si el usuario puede ver cualquier recurso del panel peso cabal
     */
    public function viewAnyPesoCabalResource(User $user): bool
    {
        return $user->canAccessPesoCabalPanel();
    }

    /**
     * Verificar si el usuario puede crear recursos en el panel agricultor
     */
    public function createAgricultorResource(User $user): bool
    {
        return $user->canAccessAgricultorPanel();
    }

    /**
     * Verificar si el usuario puede crear recursos en el panel beneficio
     */
    public function createBeneficioResource(User $user): bool
    {
        return $user->canAccessBeneficioPanel();
    }

    /**
     * Verificar si el usuario puede crear recursos en el panel peso cabal
     */
    public function createPesoCabalResource(User $user): bool
    {
        return $user->canAccessPesoCabalPanel();
    }

    /**
     * Verificar si el usuario puede editar recursos en el panel agricultor
     */
    public function updateAgricultorResource(User $user): bool
    {
        return $user->canAccessAgricultorPanel();
    }

    /**
     * Verificar si el usuario puede editar recursos en el panel beneficio
     */
    public function updateBeneficioResource(User $user): bool
    {
        return $user->canAccessBeneficioPanel();
    }

    /**
     * Verificar si el usuario puede editar recursos en el panel peso cabal
     */
    public function updatePesoCabalResource(User $user): bool
    {
        return $user->canAccessPesoCabalPanel();
    }

    /**
     * Verificar si el usuario puede eliminar recursos en el panel agricultor
     */
    public function deleteAgricultorResource(User $user): bool
    {
        return $user->canAccessAgricultorPanel();
    }

    /**
     * Verificar si el usuario puede eliminar recursos en el panel beneficio
     */
    public function deleteBeneficioResource(User $user): bool
    {
        return $user->canAccessBeneficioPanel();
    }

    /**
     * Verificar si el usuario puede eliminar recursos en el panel peso cabal
     */
    public function deletePesoCabalResource(User $user): bool
    {
        return $user->canAccessPesoCabalPanel();
    }
}
