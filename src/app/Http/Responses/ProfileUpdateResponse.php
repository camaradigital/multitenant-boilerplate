<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse as ProfileResponseContract;
use Spatie\Multitenancy\Models\Tenant;

class ProfileUpdateResponse implements ProfileResponseContract
{
    public function toResponse($request)
    {
        if (Tenant::current()) {
            return redirect('/dashboard');  // Ou rota tenant-specific, ex.: '/tenant/dashboard'
        }

        return redirect(config('fortify.home'));  // Para central
    }
}
