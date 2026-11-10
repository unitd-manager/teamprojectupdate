<?
class CP_Admin_Modules_Event_EventItemLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('event_eventItemLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'event_item'
           ,'keyField'  => 'event_item_id'
        ));
    }

    /**
     *
     * @param type $record_id
     * @return string
     */
    function beforeDeletePortalHandler($hist_record_id, $linkName){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($cpCfg['m.event.event.showEventItem'] && $linkName == "event_event#event_eventItemLink"){
            $condn = "
                event_item_id = {$hist_record_id}
            ";
            $event_contactCount = $fn->getRecordCount('event_contact', $condn);

            if($event_contactCount > 0){
                $arr = array(
                     'status' => 'error'
                    ,'message' => "You can't delete an event items with contacts linked."
                );
                return $arr;
            }
        }
    }
}
