<?php
namespace Doctor;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\DoctorController::class => function ($container) {
                return new Controller\DoctorController(
                    $container->get(\Laminas\Db\Adapter\Adapter::class),
                    $container->get(\Laminas\Authentication\AuthenticationService::class)
                ,$container->get('Config')['rbac'] ?? []);

            },
        ],
    ],
    'router' => [
        'routes' => [
            'doctor' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/doctor',
                    'defaults' => [
                        'controller' => Controller\DoctorController::class,
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
                    'edit' => [  // New
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/edit/:id',
                            'defaults' => [
                                'controller' => Controller\DoctorController::class,
                                'action' => 'edit',
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],
                    'add-prescription' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add-prescription/:patient_id',
                            'defaults' => [
                                'action' => 'addPrescription',
                            ],
                            'constraints' => [
                                'patient_id' => '[0-9]+',
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
            'doctor/doctor/index' => __DIR__ . '/../view/doctor/doctor/index.phtml',
            'doctor/doctor/add' => __DIR__ . '/../view/doctor/doctor/add.phtml',
            'doctor/doctor/edit' => __DIR__ . '/../view/doctor/doctor/edit.phtml',
            'doctor/doctor/add-prescription' => __DIR__ . '/../view/doctor/doctor/add-prescription.phtml',
        ],
    ],
];