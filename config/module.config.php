<?php
/**
 * module.config.php - Ticket Config
 *
 * Main Config File for Event Ticket Plugin
 *
 * @category Config
 * @package Event\Ticket
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Event\Ticket;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    # History Module - Routes
    'router' => [
        'routes' => [
            'event-ticket' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/event/ticket[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\TicketController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'event-ticket-setup' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/event/ticket/setup',
                    'defaults' => [
                        'controller' => Controller\InstallController::class,
                        'action'     => 'checkdb',
                    ],
                ],
            ],
        ],
    ], # Routes

    # View Settings
    'view_manager' => [
        'template_path_stack' => [
            'event-ticket' => __DIR__ . '/../view',
        ],
    ],
];
