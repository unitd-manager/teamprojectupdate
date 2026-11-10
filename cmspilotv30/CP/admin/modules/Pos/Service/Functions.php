<?
class CP_Admin_Modules_Pos_Service_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_service');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'printListScreen')
           ,'actBtnsDetail' => array('edit', 'delete', 'printListScreen')
        ));
    }

}