<?
class CP_Admin_Modules_Directory_Guide_Functions extends CP_Common_Modules_Directory_Guide_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_guide');
        $modules->registerModule($modObj, array(
            'hasFlagInList'  => 0
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'hasMultiLang'  => 1
        ));
    }
}