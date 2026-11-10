<?
class CP_Admin_Modules_Event_ContactLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('event_contactLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'event_contact'
           ,'keyField'      => 'event_contact_id'
        ));
    }
}
