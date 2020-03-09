<?php
/**
 * TicketController.php - Main Controller
 *
 * Main Controller for Event Ticket Plugin
 *
 * @category Controller
 * @package Event\Ticket
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Event\Ticket\Controller;

use Application\Controller\CoreEntityController;
use Application\Model\CoreEntityModel;
use OnePlace\Article\Model\ArticleTable;
use OnePlace\Article\Variant\Model\VariantTable;
use OnePlace\Event\Model\EventTable;
use OnePlace\Event\Ticket\Model\TicketTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;

class TicketController extends CoreEntityController {
    /**
     * Event Table Object
     *
     * @since 1.0.0
     */
    protected $oTableGateway;

    /**
     * EventController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param EventTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter,TicketTable $oTableGateway,$oServiceManager) {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'eventticket-single';
        parent::__construct($oDbAdapter,$oTableGateway,$oServiceManager);

        if($oTableGateway) {
            # Attach TableGateway to Entity Models
            if(!isset(CoreEntityModel::$aEntityTables[$this->sSingleForm])) {
                CoreEntityModel::$aEntityTables[$this->sSingleForm] = $oTableGateway;
            }
        }
    }

    public function attachTicketForm($oItem = false) {
        $oForm = CoreEntityController::$aCoreTables['core-form']->select(['form_key'=>'eventticket-single']);

        $aFields = [];
        $aUserFields = CoreEntityController::$oSession->oUser->getMyFormFields();
        if(array_key_exists('eventticket-single',$aUserFields)) {
            $aFieldsTmp = $aUserFields['eventticket-single'];
            if(count($aFieldsTmp) > 0) {
                # add all contact-base fields
                foreach($aFieldsTmp as $oField) {
                    if($oField->tab == 'ticket-base') {
                        $aFields[] = $oField;
                    }
                }
            }
        }

        $aFieldsByTab = ['ticket-base'=>$aFields];

        # Try to get adress table
        try {
            $oTicketTbl = CoreEntityController::$oServiceManager->get(TicketTable::class);
        } catch(\RuntimeException $e) {
            //echo '<div class="alert alert-danger"><b>Error:</b> Could not load address table</div>';
            return [];
        }

        # Try to get adress table
        try {
            $oVarTbl = CoreEntityController::$oServiceManager->get(VariantTable::class);
        } catch(\RuntimeException $e) {
            //echo '<div class="alert alert-danger"><b>Error:</b> Could not load address table</div>';
            return [];
        }

        # Try to get adress table
        try {
            $oArtTbl = CoreEntityController::$oServiceManager->get(ArticleTable::class);
        } catch(\RuntimeException $e) {
            //echo '<div class="alert alert-danger"><b>Error:</b> Could not load address table</div>';
            return [];
        }

        if(!isset($oTicketTbl)) {
            return [];
        }

        $aHistories = [];
        $oPrimaryTicket = false;
        if($oItem) {
            # load contact addresses
            $oHistories = $oTicketTbl->fetchAll(false, ['event_idfs' => $oItem->getID()]);
            # get primary address
            if (count($oHistories) > 0) {
                foreach ($oHistories as $oAddr) {
                    $oVar = $oVarTbl->getSingle($oAddr->article_idfs);
                    $oAddr->label = $oVar->getLabel();
                    $oAddr->ticket_price = $oVar->price;
                    $oAddr->slots_total = $oAddr->slots;
                    $oAddr->tickets_booked = 4;
                    $aHistories[] = $oAddr;
                }
            }
        }

        # Pass Data to View - which will pass it to our partial
        return [
            # must be named aPartialExtraData
            'aPartialExtraData' => [
                # must be name of your partial
                'event_ticket'=> [
                    'oTickets'=>$aHistories,
                    'oForm'=>$oForm,
                    'aFormFields'=>$aFieldsByTab,
                ]
            ]
        ];
    }

    public function attachTicketToEvent($oItem,$aRawData)
    {
        // check for article
        $oArtTbl = CoreEntityController::$oServiceManager->get(ArticleTable::class);
        $oVarTbl = CoreEntityController::$oServiceManager->get(VariantTable::class);
        $oEventTbl = CoreEntityController::$oServiceManager->get(EventTable::class);
        $oEvent = $oEventTbl->getSingle($aRawData['ref_idfs']);

        try {
            $oBaseArticle = $oArtTbl->getSingle($aRawData['ref_idfs'],'ref_idfs');
        } catch(\RuntimeException $e) {
            $oNewArt = $oArtTbl->generateNew();
            $aBaseArtData = [
                'label' => 'Event Tickets '.$oEvent->getLabel(),
                'ref_idfs' => $aRawData['ref_idfs'],
                'ref_type' => 'event',
                'created_by' => CoreEntityController::$oSession->oUser->getID(),
                'created_date' => date('Y-m-d H:i:s',time()),
                'modified_by' => CoreEntityController::$oSession->oUser->getID(),
                'modified_date' => date('Y-m-d H:i:s',time()),
            ];
            $oNewArt->exchangeArray($aBaseArtData);
            $iNewArtID = $oArtTbl->saveSingle($oNewArt);
            $oBaseArticle = $oArtTbl->getSingle($iNewArtID);
        }

        $oNewTicket = $oVarTbl->generateNew();
        $aTicketData = [
            'article_idfs' => $oBaseArticle->getID(),
            'label' => $aRawData[$this->sSingleForm.'_label'],
            'price' => (float)$aRawData[$this->sSingleForm.'_ticket_price'],
            'created_by' => CoreEntityController::$oSession->oUser->getID(),
            'created_date' => date('Y-m-d H:i:s',time()),
            'modified_by' => CoreEntityController::$oSession->oUser->getID(),
            'modified_date' => date('Y-m-d H:i:s',time()),
        ];
        $oNewTicket->exchangeArray($aTicketData);
        $iTicketID = $oVarTbl->saveSingle($oNewTicket);

        $oItem->article_idfs = $iTicketID;
        $oItem->slots = $aRawData[$this->sSingleForm.'_slots_total'];
        $oItem->fully_booked = 0;
        $oItem->event_idfs = $aRawData['ref_idfs'];

        return $oItem;
    }

    public function addAction() {
        /**
         * You can just use the default function and customize it via hooks
         * or replace the entire function if you need more customization
         *
         * Hooks available:
         *
         * contact-add-before (before show add form)
         * contact-add-before-save (before save)
         * contact-add-after-save (after save)
         */
        $iEventID = $this->params()->fromRoute('id', 0);

        return $this->generateAddView('eventticket','eventticket-single','event','view',$iEventID,['iEventID'=>$iEventID]);
    }
}
