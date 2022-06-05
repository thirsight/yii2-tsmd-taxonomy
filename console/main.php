<?php

/**
 * TSMD 模块配置文件
 *
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2008 thirsight
 * @license https://tsmd.thirsight.com/license/
 */

$dbTpl = require dirname(__DIR__) . '/../yii2-tsmd-base/config/_dbtpl-local.php';
return [
    // 设置路径别名，以便 Yii::autoload() 可自动加载 TSMD 自定的类
    'aliases' => [
        // yii2-tsmd-taxonomy 路径
        '@tsmd/taxonomy' => __DIR__ . '/../src',
    ],
    // 设置命令行模式控制器
    // ./yii migrate-taxonomy/create 'tsmd\taxonomy\migrations\M20...'
    // ./yii migrate-taxonomy/new
    // ./yii migrate-taxonomy/up
    // ./yii migrate-taxonomy/down 1
    'controllerMap' => [
        'migrate-taxonomy' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [],
            'migrationNamespaces' => [
                'tsmd\taxonomy\migrations',
            ]
        ],
    ],
];
