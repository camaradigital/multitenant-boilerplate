<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Spatie\Multitenancy\Models\Tenant;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{

    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $user?->load('roles');

        $currentTenant = Tenant::current();

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? tap([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ], function (&$data) use ($user) {
                    if ($user instanceof \App\Models\Tenant\User) {
                        $data['permissions'] = $user->getAllPermissions()->pluck('name');
                        $data['cpf'] = $user->cpf;
                        $data['profile_data'] = $user->profile_data;
                        $data['bairro_id'] = $user->bairro_id;
                        $data['profile_photo_url'] = $user->profile_photo_url ?? null;
                    } else {
                        $data['permissions'] = [];
                    }
                }) : null,
            ],

            'tenant' => $currentTenant ? $currentTenant->toArray() : null,

            'theme' => [
                'primary' => $currentTenant?->cor_primaria ?? '#4F46E5',
                'secondary' => $currentTenant?->cor_secundaria ?? '#D946EF',
            ],

            'jetstream' => [
                'canManageTwoFactorAuthentication' => class_exists(Features::class) && Features::canManageTwoFactorAuthentication(),
                'hasAccountDeletionFeatures' => class_exists(Jetstream::class) && Jetstream::hasAccountDeletionFeatures(),
                'managesProfilePhotos' => class_exists(Jetstream::class) && Jetstream::managesProfilePhotos(),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],

            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
