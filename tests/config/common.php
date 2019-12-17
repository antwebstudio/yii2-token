<?php
return [
	'id' => 'app-test',
	'basePath' => dirname(__DIR__),
	'timezone' => 'Asia/Kuala_Lumpur',
	'aliases' => [
		'ant' => dirname(dirname(__DIR__)).'/src',
		'api' => dirname(dirname(__DIR__)).'/src/api',
		'common/config' => __DIR__, // dirname(dirname(__DIR__)).'/vendor/inspirenmy/yii2-core/src/common/config',
		'common/modules/moduleManager' => dirname(dirname(__DIR__)).'/vendor/inspirenmy/yii2-core/src/common/modules/moduleManager',
		'vendor' => dirname(dirname(__DIR__)).'/vendor',
		'@common/migrations' => '@vendor/inspirenmy/yii2-core/src/common/migrations',
		'@common/rbac' => '@vendor/inspirenmy/yii2-core/src/common/rbac',
	],
	'bootstrap' => ['gii'],
	'modules' => [
		'gii' => [
			'class' => 'yii\gii\Module',
		],
	],
    'components' => [
		'notifier' => [
           'class' => '\tuyakhov\notifications\Notifier',
		   'on '.\tuyakhov\notifications\Notifier::EVENT_AFTER_SEND => function($event) {
			   if ($event->response === true) {
			   } else if ($event->response !== true) {
					if (YII_DEBUG) throw new \Exception('Response: '.$event->response);
			   }
		   },
           'channels' => [
               'mail' => [
                   'class' => '\ant\notifications\channels\MailChannel',
                   'from' => ['chy1988@gmail.com' => 'test application'],
				   'developerEmail' => 'chy1988@gmail.com',
               ],
			],
		],
		'urlManagerFrontEnd' => [
			'class' => 'yii\web\UrlManager',
		],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;port=3306;dbname=test_test',
            'username' => 'root',
            'password' => 'root',
            'tablePrefix' => '',
            'charset' => 'utf8',
			'on afterOpen' => function($event) { 
				$now = new \DateTime();
				$mins = $now->getOffset() / 60;
				$sgn = ($mins < 0 ? -1 : 1);
				$mins = abs($mins);
				$hrs = floor($mins / 60);
				$mins -= $hrs * 60;
				$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
				$event->sender->createCommand("SET time_zone='$offset';")->execute(); 
			},
        ],
        'moduleManager' => [
            'class' => 'ant\moduleManager\components\ModuleManager',
			'moduleAutoloadPaths' => [
				'@ant', 
				'@vendor/inspirenmy/yii2-ecommerce/src/common/modules', 
				'@vendor/inspirenmy/yii2-user/src/common/modules',
				'@vendor/inspirenmy/yii2-core/src/common/modules',
			],
        ],
        'user' => [
			'class' => 'yii\web\User',
            'identityClass' => 'ant\user\models\User',
        ],
        'mailer' => [
            'syncMailer' => [
                'useFileTransport' => true,
                'viewPath' => '@tests/mail',
            ],
        ],
	],
	'controllerMap' => [
		'module' => [
			'class' => 'ant\moduleManager\console\controllers\DefaultController',
		],
		'migrate' => [
			'class' => 'ant\moduleManager\console\controllers\MigrateController',
            'migrationNamespaces' => [
                'yii\queue\db\migrations',
				'ant\moduleManager\migrations\db',
			],
			'migrationPath' => [
                '@yii/rbac/migrations',
				'@common/migrations/db',
				'@tests/migrations/db',
			],
            'migrationTable' => '{{%system_db_migration}}'
		],
		'rbac-migrate' => [
			'class' => 'ant\moduleManager\console\controllers\RbacMigrateController',
            'migrationPath' => [
                '@common/migrations/rbac',
            ],
            'migrationTable' => '{{%system_rbac_migration}}',
            'migrationNamespaces' => [
                'ant\moduleManager\migrations\rbac',
			],
            'templateFile' => '@common/rbac/views/migration.php'
		],
	],
];