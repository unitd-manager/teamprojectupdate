<?
class CP_Admin_Modules_Party_PartySetup_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('party_partySetup');
        $modObj['tableName'] = 'party_setup';
        $modObj['keyField']  = 'party_setup_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title' => 'Party Setup'
           ,'relatedTables' => array('recipient_list', 'guest_list', 'media', 'message')
        ));
    }

    function setMediaArray($mediaArr) {
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('party_partySetup', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('party_partySetup', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('party_partySetup', 'party_messageLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'message'
            ,'showAnchorInLinkPortal' => 1
            ,'hasModalChoose' => false
           ,'fieldlabel' => array('Subject', 'Date')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('party_partySetup', 'ecommerce_orderLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'  => 'order'
           ,'showAnchorInLinkPortal' => 0
           ,'hasModalChoose' => false
           ,'fieldlabel' => array(
               'Order Id'
              ,'Order Date'
              ,'Guest Name'
              ,'Guest Email'
              ,'Order Amount'
              ,'Payment Method'
              ,'Status'
            )
           ,'anchorFieldsArr' => array('order_code' => $inst->getLinkAnchorObj('order_code', 'order_id'))
           ,'summaryFieldsArray' => array(
               'order_amount'
            )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('party_partySetup', 'party_guestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'  => 'guest_list'
           ,'showAnchorInLinkPortal' => 0
           ,'hasModalChoose' => false
           ,'fieldlabel' => array(
               'Guest Name'
              ,'Email'
            )
        ));
    }

    function getPartyPartySetupEcommerceOrderLinkPortalSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $order_status = $fn->getReqParam('order_status');

        $text = "
        <select name='order_status'  class='float_right m5'>
            <option value=''>Status</option>
            {$cpUtil->getDropDown1($cpCfg['m.ecommerce.order.statusArr'], $order_status)}
        </select>
        ";

        return $text;
    }

}