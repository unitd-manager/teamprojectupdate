<?
class CP_Admin_Modules_WebBasic_Career_Functions extends CP_Common_Modules_WebBasic_Career_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('webBasic_career');
        $modules->registerModule($modObj, array(
            'hasFlagInList'  => 0
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'hasMultiLang'  => 1
        ));
    }
}