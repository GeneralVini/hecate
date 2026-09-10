<?php

return [
    'class' => 'yii\\db\\Connection',
    'dsn' => getenv('HECATE_DB_DSN') ?: 'pgsql:host=127.0.0.1;port=5432;dbname=hecate',
    'username' => getenv('HECATE_DB_USER') ?: 'hecate',
    'password' => getenv('HECATE_DB_PASSWORD') ?: '',
    'charset' => 'utf8',
];
