<?php

return array_merge(
    [
        'class' => 'yii\db\Connection',
        'charset' => 'utf8',
    ],
    require(__DIR__ . '/db-local.php')
);
