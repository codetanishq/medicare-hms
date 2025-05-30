<?php
namespace Appointment;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\AppointmentController::class => function ($container) {
                return new Controller\AppointmentController(
                    $container->get(\Laminas\Db\Adapter\Adapter::class),
                    $container->get(\Laminas\Authentication\AuthenticationService::class)
                    ,$container->get('Config')['rbac'] ?? []);
                
            },
        ],
    ],
    'router' => [
        'routes' => [
            'appointment' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/appointment',
                    'defaults' => [
                        'controller' => Controller\AppointmentController::class,
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
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/edit/:id',
                            'defaults' => [
                                'controller' => Controller\AppointmentController::class,
                                'action' => 'edit',
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
            'appointment/appointment/index' => __DIR__ . '/../view/appointment/appointment/index.phtml',
            'appointment/appointment/add' => __DIR__ . '/../view/appointment/appointment/add.phtml',
            'appointment/appointment/edit' => __DIR__ . '/../view/appointment/appointment/edit.phtml',
        ],
    ],
];