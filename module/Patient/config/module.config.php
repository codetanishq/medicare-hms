<?php
namespace Patient;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\PatientController::class => function ($container) {
                return new Controller\PatientController(
                    $container->get(\Laminas\Db\Adapter\Adapter::class),
                    $container->get(\Laminas\Authentication\AuthenticationService::class),
                    $container->get('Config')['rbac'] ?? []
                );
            },
        ],
    ],
    'router' => [
        'routes' => [
            'patient' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/patient',
                    'defaults' => [
                        'controller' => Controller\PatientController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'add' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'controller' => Controller\PatientController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [  // New
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/edit/:id',
                            'defaults' => [
                                'controller' => Controller\PatientController::class,
                                'action' => 'edit',
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],
                    'profile' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/profile/:id',
                            'defaults' => [
                                'controller' => Controller\PatientController::class,
                                'action' => 'profile',
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'patient/patient/index' => __DIR__ . '/../view/patient/patient/index.phtml',
            'patient/patient/add' => __DIR__ . '/../view/patient/patient/add.phtml',
            'patient/patient/edit' => __DIR__ . '/../view/patient/patient/edit.phtml',
            'patient/patient/profile' => __DIR__ . '/../view/patient/patient/profile.phtml',
        ],
    ],
];