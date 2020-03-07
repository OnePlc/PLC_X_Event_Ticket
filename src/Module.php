<?php
/**
 * Module.php - Module Class
 *
 * Module Class File for Event Ticket Plugin
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

use Application\Controller\CoreEntityController;
use Laminas\Mvc\MvcEvent;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\EventManager\EventInterface as Event;
use Laminas\ModuleManager\ModuleManager;
use OnePlace\Event\Ticket\Controller\TicketController;
use OnePlace\Event\Ticket\Model\TicketTable;

class Module {
    /**
     * Module Version
     *
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * Load module config file
     *
     * @since 1.0.0
     * @return array
     */
    public function getConfig() : array {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(Event $e)
    {
        // This method is called once the MVC bootstrapping is complete
        $application = $e->getApplication();
        $container    = $application->getServiceManager();
        $oDbAdapter = $container->get(AdapterInterface::class);
        $tableGateway = $container->get(TicketTable::class);

        # Register Filter Plugin Hook
        CoreEntityController::addHook('event-view-before',(object)['sFunction'=>'attachTicketForm','oItem'=>new TicketController($oDbAdapter,$tableGateway,$container)]);
        CoreEntityController::addHook('eventticket-add-before-save',(object)['sFunction'=>'attachTicketToEvent','oItem'=>new TicketController($oDbAdapter,$tableGateway,$container)]);
    }

    /**
     * Load Models
     */
    public function getServiceConfig() : array {
        return [
            'factories' => [
                # Ticket Plugin - Base Model
                Model\TicketTable::class => function($container) {
                    $tableGateway = $container->get(Model\TicketTableGateway::class);
                    return new Model\TicketTable($tableGateway,$container);
                },
                Model\TicketTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Ticket($dbAdapter));
                    return new TableGateway('event_ticket', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    } # getServiceConfig()

    /**
     * Load Controllers
     */
    public function getControllerConfig() : array {
        return [
            'factories' => [
                Controller\TicketController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    $tableGateway = $container->get(TicketTable::class);

                    # hook start
                    # hook end
                    return new Controller\TicketController(
                        $oDbAdapter,
                        $tableGateway,
                        $container
                    );
                },
                # Installer
                Controller\InstallController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\InstallController(
                        $oDbAdapter,
                        $container->get(Model\TicketTable::class),
                        $container
                    );
                },
            ],
        ];
    } # getControllerConfig()
}
