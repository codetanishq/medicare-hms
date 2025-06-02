<?php
return [
    'db' => [
        'driver' => 'Pdo_Mysql',
        'database' => 'medicare_hms',
        'username' => 'root',
        'password' => '',
        'hostname' => 'localhost',
    ],
    'session_config' => [
        'name' => 'medicare_hms_session',
        'use_cookies' => true,
        'cookie_lifetime' => 3600,
        'gc_maxlifetime' => 3600,
    ],
    'service_manager' => [
        'factories' => [
            \Laminas\Authentication\AuthenticationService::class => function ($container) {
                $adapter = new \Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter(
                    $container->get(\Laminas\Db\Adapter\Adapter::class),
                    'users',
                    'username',
                    'password',
                    function ($hash, $password) {
                        return md5($password) === $hash;
                    }
                );
                $storage = new \Laminas\Authentication\Storage\Session();
                return new \Laminas\Authentication\AuthenticationService($storage, $adapter);
            },
            \Laminas\Session\SessionManager::class => \Laminas\Session\Service\SessionManagerFactory::class,
        ],
    ],
    
];