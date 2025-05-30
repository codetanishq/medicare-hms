<?php
namespace Admin;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\AdminController::class => function ($container) {
                return new Controller\AdminController(
                    $container->get(\Laminas\Db\Adapter\Adapter::class)
                );
            },
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/admin',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'index',
                    ],
                ],
                    'staff' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/staff[/:action[/:id]]',
                            'defaults' => [
                                'action' => 'index',
                            ],
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
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
            'admin/admin/index' => __DIR__ . '/../view/admin/admin/index.phtml',
            'admin/admin/staff' => __DIR__ . '/../view/admin/admin/staff.phtml',
            'admin/admin/add-staff' => __DIR__ . '/../view/admin/admin/add-staff.phtml',
        ],
    ],
];