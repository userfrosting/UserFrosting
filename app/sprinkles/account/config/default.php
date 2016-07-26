<?php

    /**
     * Account configuration file for UserFrosting.
     *
     */
     
    return [      
        'reserved_user_ids' => [
            'guest'  => -1,
            'master' => 1
        ],
        'remember_me_table' => [
            'tableName' => 'user_rememberme',
            'credentialColumn' => 'user_id',
            'tokenColumn' => 'token',
            'persistentTokenColumn' => 'persistent_token',
            'expiresColumn' => 'expires'
        ]
    ];
    