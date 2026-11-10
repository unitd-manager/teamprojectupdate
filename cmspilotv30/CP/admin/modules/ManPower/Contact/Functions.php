<?
class CP_Admin_Modules_ManPower_Contact_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $actBtnsList = array('export', 'import');
        if($cpCfg['cp.showDeleteActionBtnInList']){
            $actBtnsList[] = 'deleteList';
        } 
        
        $modObj = $modules->getModuleObj('manPower_contact');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media')
           ,'actBtnsList'   => $actBtnsList
           ,'hasMultiLang'  => 1
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_contact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_contact', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_contact', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_contact', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'interest_contact'
            ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_contact', 'webBasic_contentLink');
        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'contact_content'
           ,'showAnchorInLinkPortal' => 0
        ));

        if($cpCfg['m.manPower.contact.showOrders']){
            $linkObj = $inst->getLinksArrayObj('manPower_contact', 'ecommerce_orderLink');
            $inst->registerLinksArray($linkObj, array(
                'historyTableName' => "order"
               ,'showAnchorInLinkPortal' => 0
            ));            
        }        
        
        //------------------------------------------------------------------------------//
        if($cpCfg['m.manPower.contact.showEvent']){
            $linkObj = $inst->getLinksArrayObj('manPower_contact', 'event_eventLink');

            $inst->registerLinksArray($linkObj, array(
                'historyTableName' => 'event_contact'
               ,'showAnchorInLinkPortal' => 0
               ,'hasModalChoose' => false
            ));
        }
    }
}