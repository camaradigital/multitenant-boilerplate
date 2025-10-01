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
     * CORREÇÃO: Alinhado com o guard 'tenant' que definimos em config/auth.php.
     */
    'default_auth_driver' => 'tenant',

    /**
     * O modelo a ser usado para o log de atividades.
     * Sua configuração está correta, apontando para um model personalizado.
     */
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,

    /**
     * O nome da tabela a ser usada para o log de atividades.
     */
    'table_name' => 'activity_log',

    /**
     * O nome da conexão do banco de dados a ser usada.
     * Sua configuração está correta, forçando o uso da conexão do tenant.
     */
    'database_connection' => 'tenant',
];
