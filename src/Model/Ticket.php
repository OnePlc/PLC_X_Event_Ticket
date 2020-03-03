<?php
/**
 * History.php - Ticket Entity
 *
 * Entity Model for Event Ticket
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

use Application\Model\CoreEntityModel;

class Ticket extends CoreEntityModel {
    /**
     * History constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @since 1.0.0
     */
    public function __construct($oDbAdapter) {
        parent::__construct($oDbAdapter);

        # Set Single Form Name
        $this->sSingleForm = 'eventticket-single';

        # Attach Dynamic Fields to Entity Model
        $this->attachDynamicFields();
    }

    /**
     * Set Entity Data based on Data given
     *
     * @param array $aData
     * @since 1.0.0
     */
    public function exchangeArray(array $aData) {
        $this->id = !empty($aData['Ticket_ID']) ? $aData['Ticket_ID'] : 0;

        $this->updateDynamicFields($aData);
    }
}