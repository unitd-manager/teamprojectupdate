<?
class CP_Admin_Modules_Pos_Warehouse_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_warehouse');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
        ));
    }
 
}