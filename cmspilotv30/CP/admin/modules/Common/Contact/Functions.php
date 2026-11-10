<?
class CP_Admin_Modules_Common_Contact_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $actBtnsList = array('new', 'export', 'import');
        if($cpCfg['cp.showDeleteActionBtnInList']){
            $actBtnsList[] = 'deleteList';
        } 
        
        $modObj = $modules->getModuleObj('common_contact');
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
        $mediaObj = $mediaArr->getMediaObj('common_contact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('common_contact', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('common_contact', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('common_contact', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'interest_contact'
            ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('common_contact', 'webBasic_contentLink');
        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'contact_content'
           ,'showAnchorInLinkPortal' => 0
        ));

        if($cpCfg['m.common.contact.showOrders']){
            $linkObj = $inst->getLinksArrayObj('common_contact', 'ecommerce_orderLink');
            $inst->registerLinksArray($linkObj, array(
                'historyTableName' => "order"
               ,'showAnchorInLinkPortal' => 0
            ));            
        }        
        
        //------------------------------------------------------------------------------//
        if($cpCfg['m.common.contact.showEvent']){
            $linkObj = $inst->getLinksArrayObj('common_contact', 'event_eventLink');

            $inst->registerLinksArray($linkObj, array(
                'historyTableName' => 'event_contact'
               ,'showAnchorInLinkPortal' => 0
               ,'hasModalChoose' => false
            ));
        }
    }
}