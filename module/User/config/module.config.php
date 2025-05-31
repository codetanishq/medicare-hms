<?php
namespace User;

use Laminas\Router\Http\Literal;

return [
    'controllers' => [
        'factories' => [
            Controller\AuthController::class => function ($container) {
                return new Controller\AuthController(
                    $container->get(\Laminas\Authentication\AuthenticationService::class),
                    $container->get(\Laminas\Db\Adapter\Adapter::class)
                );
            },
        ],
    ],
    'router' => [
        'routes' => [
            'login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'dashboard' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/dashboard',
                    'defaults' => [
                        'controller' => \Application\Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\IsAllowed::class => function ($container) {
                return new View\Helper\IsAllowed($container->get('Config')['rbac']);
            },
        ],
        'aliases' => [
            'isAllowed' => View\Helper\IsAllowed::class,
        ],
    ],
    'rbac' => [
        'roles' => [
            'guest' => [],
            'patient' => ['guest'],
            'receptionist' => ['patient'],
            'doctor' => ['receptionist'],
            'admin' => ['doctor'],
        ],
        'permissions' => [
        'patient' => [
        'patient.view',
        'appointment.view',
    ],
    'receptionist' => [
        'patient.add',
        'appointment.add',
        'patient.edit',     
        'appointment.edit',
    ],
    'doctor' => [
        'patient.add',
        'appointment.add',
        'prescription.add',
        'patient.medical_records',
        'doctor.view',
    ],
    'admin' => [
        'patient.view',
        'appointment.view',
        'patient.add',
        'appointment.add',
        'prescription.add',
        'patient.medical_records',
        'staff.manage',
        'report.generate',
        'doctor.add',
        'doctor.view',
        'patient.edit',     
        'appointment.edit',  
        'doctor.edit',
    ],
    ],
    
],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'user/auth/login' => __DIR__ . '/../view/user/auth/login.phtml',
        ],
    ],
];