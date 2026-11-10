<?
class CP_Admin_Modules_Pos_Payment_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_payment');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'printListScreen')
        ));
    }

}