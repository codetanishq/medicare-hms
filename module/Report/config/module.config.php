<?php
namespace Report;

use Laminas\Router\Http\Literal;

return [
    'controllers' => [
        'factories' => [
            Controller\ReportController::class => function ($container) {
                return new Controller\ReportController(
                    $container->get(\Laminas\Db\Adapter\Adapter::class)
                );
            },
        ],
    ],
    'router' => [
        'routes' => [
            'report' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/report',
                    'defaults' => [
                        'controller' => Controller\ReportController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
            'child_routes' => [
                'pdf' => [
                    'type' => Literal::class,
                    'options' => [
                        'route' => '/pdf',
                        'defaults' => [
                            'action' => 'generatePdf',
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
            'report/report/index' => __DIR__ . '/../view/report/report/index.phtml',
        ],
    ],
];