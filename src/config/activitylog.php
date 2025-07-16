<?php

return [

    /**
     * Quando definido como true, o log de atividades será habilitado.
     */
    'enabled' => env('ACTIVITY_LOG_ENABLED', true),

    /**
     * Limpa o log de atividades ao deletar o registro relacionado.
     */
    'delete_records_when_model_is_deleted' => env('ACTIVITY_LOG_DELETE_RECORDS_WHEN_MODEL_IS_DELETED', true),

    /**
     * O nome do log padrão a ser usado quando nenhum nome de log for especificado.
     */
    'default_log_name' => 'default',

    /**
     * O driver de autenticação padrão a ser usado para registrar o causador.
     */
    'default_auth_driver' => 'web',

    /**
     * O modelo a ser usado para o log de atividades.
     * Deve ser uma classe que estende \Spatie\Activitylog\Models\Activity.
     */
    'activity_model' => \App\Models\Tenant\ActivityLog::class,

    /**
     * O nome da tabela a ser usada para o log de atividades.
     */
    'table_name' => 'activity_log',

    /**
     * O nome da conexão do banco de dados a ser usada.
     * Deixe como `null` para usar a conexão de banco de dados padrão.
     */
    // CORREÇÃO: Força o pacote a usar a conexão 'tenant', que é gerenciada
    // dinamicamente pelo pacote de multitenancy. Isso resolve o erro.
    'database_connection' => 'tenant',
];
