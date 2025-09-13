<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Tenant;

class HealthCheckController extends Controller
{
    /**
     * Retorna o status de saúde da aplicação e seus serviços.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $status = [
            'app' => 'up',
            'central_database' => 'down',
            'redis' => 'down',
            'tenants_databases' => []
        ];

        // 1. Verifica a conexão com o banco de dados central
        try {
            DB::connection('mysql')->getPdo();
            $status['central_database'] = 'up';
        } catch (\Exception $e) {
            // Em caso de falha, retorna imediatamente com status 500
            return response()->json($status, 500);
        }

        // 2. Verifica a conexão com o Redis
        try {
            Redis::ping();
            $status['redis'] = 'up';
        } catch (\Exception $e) {
            // Em caso de falha, retorna imediatamente com status 500
            return response()->json($status, 500);
        }

        // 3. Itera sobre cada tenant e verifica a conexão do banco de dados
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);
                DB::connection('tenant')->getPdo();
                $status['tenants_databases'][$tenant->subdomain] = 'up';
            } catch (\Exception $e) {
                // Se um tenant falhar, registramos o erro e continuamos, mas retornamos um erro 500 no final
                $status['tenants_databases'][$tenant->subdomain] = 'down';
            } finally {
                tenancy()->end();
            }
        }

        $allTenantsUp = !in_array('down', $status['tenants_databases']);

        if ($status['central_database'] === 'up' && $status['redis'] === 'up' && $allTenantsUp) {
            return response()->json($status, 200);
        }

        return response()->json($status, 500);
    }
}
