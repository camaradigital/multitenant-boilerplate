<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant; // Use o seu modelo Tenant
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Importar o Facade de Rota
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $currentTenant = Tenant::current();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            // CORREÇÃO: Passando todos os dados do tenant para o frontend
            'tenant' => $currentTenant ? $currentTenant->toArray() : null,
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'versions' => [
                'php' => phpversion(),
                'laravel' => app()->version(),
            ],
            // CORREÇÃO: Garantindo que canLogin e canRegister sejam passados
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}
