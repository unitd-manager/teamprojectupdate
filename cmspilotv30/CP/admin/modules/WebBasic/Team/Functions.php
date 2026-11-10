<?
class CP_Admin_Modules_WebBasic_Team_Functions extends CP_Common_Modules_WebBasic_Team_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('webBasic_team');
        $modules->registerModule($modObj, array(
            'hasFlagInList'  => 0
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'hasMultiLang'  => 1
        ));
    }
}