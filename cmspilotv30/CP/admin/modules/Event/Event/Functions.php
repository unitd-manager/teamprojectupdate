<?
class CP_Admin_Modules_Event_Event_Functions extends CP_Common_Modules_Event_Event_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');

        $actionArr = array('edit', 'delete');
        if($cpCfg['m.event.event.hasDuplicate']){
            $actionArr[] = 'duplicate';
        }

        $modObj = $modules->getModuleObj('event_event');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsDetail' => $actionArr
        ));
    }


     /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('event_event', 'common_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'event_contact'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('event_event', 'event_eventItemLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'event_item'
           ,'linkingType'  => 'portal'
           ,'hasPortalEdit' => true
           ,'hasPortalDelete' => true
           ,'fieldlabel' => array('Title', 'Price', 'Sort Order')
           ,'additionalFieldsArray'=> array('b.sort_order')
           ,'showAnchorInLinkPortal'=> false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('event_event', 'web2_tagsLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'            => 'tags_history'
           ,'showAnchorInLinkPortal'      => 0
        ));

        if($cpCfg['cp.hasMultiSites']){
            $siteObj = getCPFnObj('common_site');
            $siteObj->setLinksArrayForSiteLink($inst, 'event_event');
        }
  }

    /**
     *
     * @param type $record_id
     * @return string
     */
    function beforeDeleteHandler($record_id){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($cpCfg['m.event.event.showContact']){
            $condn = "
                event_id = {$record_id}
            ";
            $event_contactCount = $fn->getRecordCount('event_contact', $condn);

            if($event_contactCount > 0){
                $arr = array(
                     'status' => 'error'
                    ,'message' => "You can't delete an event with contacts linked."
                );
                return $arr;
            }
        }
    }
}