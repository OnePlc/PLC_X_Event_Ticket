<?php
/**
 * TicketTable.php - Ticket Table
 *
 * Table Model for Ticket Ticket
 *
 * @category Model
 * @package Event\Ticket
 * @author Verein onePlace
 * @copyright (C) 2020 Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Event\Ticket\Model;

use Application\Controller\CoreController;
use Application\Model\CoreEntityTable;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;

class TicketTable extends CoreEntityTable {

    /**
     * TicketTable constructor.
     *
     * @param TableGateway $tableGateway
     * @since 1.0.0
     */
    public function __construct(TableGateway $tableGateway) {
        parent::__construct($tableGateway);

        # Set Single Form Name
        $this->sSingleForm = 'eventticket-single';
    }

    /**
     * Get Ticket Entity
     *
     * @param int $id
     * @return mixed
     * @since 1.0.0
     */
    public function getSingle($id) {
        # Use core function
        return $this->getSingleEntity($id,'Ticket_ID');
    }

    /**
     * Save Ticket Entity
     *
     * @param Ticket $oTicket
     * @return int Ticket ID
     * @since 1.0.0
     */
    public function saveSingle(Ticket $oTicket) {
        $aData = [
            'article_idfs' => $oTicket->article_idfs,
            'event_idfs' => $oTicket->event_idfs,
            'slots' => $oTicket->slots,
            'fully_booked' => $oTicket->fully_booked,
        ];

        //$aData = $this->attachDynamicFields($aData,$oTicket);

        $id = (int) $oTicket->id;

        if ($id === 0) {
            # Add Metadata
            $aData['created_by'] = CoreController::$oSession->oUser->getID();
            $aData['created_date'] = date('Y-m-d H:i:s',time());
            $aData['modified_by'] = CoreController::$oSession->oUser->getID();
            $aData['modified_date'] = date('Y-m-d H:i:s',time());

            # Insert Ticket
            $this->oTableGateway->insert($aData);

            # Return ID
            return $this->oTableGateway->lastInsertValue;
        }

        # Check if Ticket Entity already exists
        try {
            $this->getSingle($id);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf(
                'Cannot update Ticket with identifier %d; does not exist',
                $id
            ));
        }

        # Update Metadata
        $aData['modified_by'] = CoreController::$oSession->oUser->getID();
        $aData['modified_date'] = date('Y-m-d H:i:s',time());

        # Update Ticket
        $this->oTableGateway->update($aData, ['Ticket_ID' => $id]);

        return $id;
    }

    /**
     * Generate new single Entity
     *
     * @return Ticket
     * @since 1.0.0
     */
    public function generateNew() {
        return new Ticket($this->oTableGateway->getAdapter());
    }
}